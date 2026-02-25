<?php

namespace App\Http\Controllers;

use App\Models\CustomField;
use App\Models\CustomFieldValue;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserImportWizardController extends Controller
{
    // ─── Step 1 ──────────────────────────────────────────────────────────────

    /**
     * Show the CSV upload form (Step 1).
     */
    public function step1()
    {
        // Clear any stale wizard state from a previous session
        session()->forget([
            'wizard_import_path',
            'wizard_import_headers',
            'wizard_import_preview',
            'wizard_import_mapping',
            'wizard_import_total',
        ]);

        return view('users.import.step1');
    }

    /**
     * Handle the CSV upload, parse headers, and redirect to the mapping step.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $path     = $request->file('csv_file')->store('imports/tmp', 'local');
        $fullPath = Storage::disk('local')->path($path);

        $handle = fopen($fullPath, 'r');
        if ($handle === false) {
            Storage::disk('local')->delete($path);
            return back()->withErrors(['csv_file' => 'Could not read the uploaded file.']);
        }

        $headers = fgetcsv($handle);
        if (empty($headers)) {
            fclose($handle);
            Storage::disk('local')->delete($path);
            return back()->withErrors(['csv_file' => 'The CSV file appears to have no header row.']);
        }

        // Trim whitespace from each header
        $headers = array_map('trim', $headers);

        $previewRows = [];
        $total       = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $total++;
            if (count($previewRows) < 5) {
                $previewRows[] = array_map('trim', $row);
            }
        }
        fclose($handle);

        if ($total === 0) {
            Storage::disk('local')->delete($path);
            return back()->withErrors(['csv_file' => 'The CSV file contains no data rows.']);
        }

        session([
            'wizard_import_path'    => $path,
            'wizard_import_headers' => $headers,
            'wizard_import_preview' => $previewRows,
            'wizard_import_total'   => $total,
        ]);

        return redirect()->route('users.import.map');
    }

    // ─── Step 2 ──────────────────────────────────────────────────────────────

    /**
     * Show the column-mapping form (Step 2).
     */
    public function map()
    {
        if (!session('wizard_import_headers')) {
            return redirect()->route('users.import')
                ->withErrors(['csv_file' => 'Please upload a CSV file first.']);
        }

        $headers      = session('wizard_import_headers');
        $preview      = session('wizard_import_preview', []);
        $total        = session('wizard_import_total', 0);
        $customFields = CustomField::active()->ordered()->get();
        $appFields    = $this->buildAppFields($customFields);
        $suggested    = $this->autoDetect($headers, $appFields);

        return view('users.import.step2', compact(
            'headers', 'preview', 'total', 'appFields', 'customFields', 'suggested'
        ));
    }

    /**
     * Store the column mapping and redirect to the confirmation step.
     */
    public function storeMapping(Request $request)
    {
        $mapping = $request->input('mapping', []);

        // Ensure at least one column is mapped to email
        if (!in_array('email', $mapping, true)) {
            return back()
                ->withErrors(['mapping' => 'You must map at least one column to "Email Address" — it is required to identify users.'])
                ->withInput();
        }

        session(['wizard_import_mapping' => $mapping]);

        return redirect()->route('users.import.confirm');
    }

    // ─── Step 3 ──────────────────────────────────────────────────────────────

    /**
     * Show the import confirmation / preview (Step 3).
     */
    public function confirm()
    {
        if (!session('wizard_import_headers') || !session('wizard_import_mapping')) {
            return redirect()->route('users.import');
        }

        $headers      = session('wizard_import_headers');
        $mapping      = session('wizard_import_mapping');
        $path         = session('wizard_import_path');
        $total        = session('wizard_import_total', 0);
        $customFields = CustomField::active()->ordered()->get();
        $appFields    = $this->buildAppFields($customFields);

        // Build a preview of the first 10 resolved rows
        $fullPath = Storage::disk('local')->path($path);
        $handle   = fopen($fullPath, 'r');
        fgetcsv($handle); // skip the header row

        $previewRows = [];
        $count       = 0;

        while (($row = fgetcsv($handle)) !== false && $count < 10) {
            $raw      = array_combine($headers, array_pad(array_map('trim', $row), count($headers), ''));
            $resolved = [];

            foreach ($mapping as $col => $field) {
                if ($field === 'skip' || empty($field)) {
                    continue;
                }
                $resolved[$field] = $raw[$col] ?? '';
            }

            $previewRows[] = $resolved;
            $count++;
        }
        fclose($handle);

        // Which app fields are actually mapped (non-skip, de-duped)
        $usedFields = collect($mapping)
            ->filter(fn($f) => $f && $f !== 'skip')
            ->unique()
            ->values()
            ->all();

        return view('users.import.step3', compact(
            'mapping', 'appFields', 'usedFields', 'previewRows', 'total'
        ));
    }

    // ─── Execute ─────────────────────────────────────────────────────────────

    /**
     * Run the import and redirect to the user index.
     */
    public function execute(Request $request)
    {
        $mapping = session('wizard_import_mapping', []);
        $path    = session('wizard_import_path');
        $headers = session('wizard_import_headers');

        if (!$path || !$headers) {
            return redirect()->route('users.import');
        }

        $customFields = CustomField::active()->ordered()->get()->keyBy('field_key');
        $departments  = Department::all()->keyBy(fn($d) => strtolower($d->name));

        $fullPath = Storage::disk('local')->path($path);
        $handle   = fopen($fullPath, 'r');
        fgetcsv($handle); // skip the header row

        $created = 0;
        $skipped = 0;
        $failed  = 0;
        $errors  = [];
        $rowNum  = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNum++;
            $raw = array_combine($headers, array_pad(array_map('trim', $row), count($headers), ''));

            $userData        = [];
            $customFieldData = [];

            foreach ($mapping as $col => $field) {
                if ($field === 'skip' || empty($field)) {
                    continue;
                }

                $value = $raw[$col] ?? '';

                if (str_starts_with($field, 'cf_')) {
                    // Custom field
                    $customFieldData[substr($field, 3)] = $value;
                } elseif ($field === 'department') {
                    // Look up department by name
                    $deptName = strtolower(trim($value));
                    if ($deptName && isset($departments[$deptName])) {
                        $userData['_department_id'] = $departments[$deptName]->id;
                    }
                } else {
                    $userData[$field] = $value;
                }
            }

            // Email is required
            if (empty($userData['email']) || !filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "Row {$rowNum}: Invalid or missing email — skipped.";
                $failed++;
                continue;
            }

            // Skip users that already exist
            if (User::where('email', $userData['email'])->exists()) {
                $skipped++;
                continue;
            }

            // Default password: firstnamelastname! (lower-cased)
            if (empty($userData['password'])) {
                $firstName = $userData['first_name'] ?? '';
                $lastName  = $userData['last_name']  ?? '';
                $userData['password'] = strtolower($firstName . $lastName . '!');
            }
            $userData['password'] = Hash::make($userData['password']);

            $deptId = $userData['_department_id'] ?? null;
            unset($userData['_department_id']);

            try {
                $user = User::create($userData);

                if ($deptId) {
                    $user->departments()->attach($deptId);
                }

                foreach ($customFieldData as $fieldKey => $value) {
                    if (!$customFields->has($fieldKey)) {
                        continue;
                    }
                    CustomFieldValue::updateOrCreate(
                        ['user_id' => $user->id, 'custom_field_id' => $customFields[$fieldKey]->id],
                        ['value' => $value]
                    );
                }

                $created++;
            } catch (\Exception $e) {
                $errors[] = "Row {$rowNum}: " . $e->getMessage();
                $failed++;
            }
        }

        fclose($handle);
        Storage::disk('local')->delete($path);

        session()->forget([
            'wizard_import_path',
            'wizard_import_headers',
            'wizard_import_preview',
            'wizard_import_mapping',
            'wizard_import_total',
        ]);

        $message = "Import complete: {$created} " . str('user')->plural($created) . " created";
        if ($skipped) {
            $message .= ", {$skipped} already existed (skipped)";
        }
        if ($failed) {
            $message .= ", {$failed} failed";
        }
        $message .= '.';

        return redirect()->route('users.index')
            ->with('success', ['message' => $message])
            ->with('import_errors', $errors);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Build the list of available application fields for the mapping dropdowns.
     */
    protected function buildAppFields($customFields): array
    {
        $fields = [
            'skip'       => '— Skip this column —',
            'email'      => 'Email Address ★ (required)',
            'name'       => 'Display Name / Alias',
            'first_name' => 'Legal First Name',
            'last_name'  => 'Legal Last Name',
            'password'   => 'Password (plain text)',
            'pronouns'   => 'Pronouns',
            'notes'      => 'Notes',
            'active'     => 'Active Status (1 or 0)',
            'department' => 'Department (matched by name)',
        ];

        foreach ($customFields as $cf) {
            $fields["cf_{$cf->field_key}"] = "Custom: {$cf->name}";
        }

        return $fields;
    }

    /**
     * Attempt to auto-detect column → field mappings based on header name similarity.
     */
    protected function autoDetect(array $headers, array $appFields): array
    {
        $aliases = [
            'email'      => ['email', 'email address', 'e-mail', 'mail'],
            'name'       => ['name', 'alias', 'display name', 'username', 'furname', 'furry name', 'nickname'],
            'first_name' => ['first name', 'first_name', 'firstname', 'legal first', 'given name', 'first'],
            'last_name'  => ['last name', 'last_name', 'lastname', 'surname', 'family name', 'legal last', 'last'],
            'password'   => ['password', 'pass', 'pwd'],
            'pronouns'   => ['pronouns', 'pronoun'],
            'notes'      => ['notes', 'note', 'comments', 'comment'],
            'active'     => ['active', 'enabled', 'status'],
            'department' => ['department', 'dept', 'team', 'division'],
        ];

        $map = [];

        foreach ($headers as $header) {
            $normalized = strtolower(trim($header));
            $matched    = 'skip';

            foreach ($aliases as $field => $variations) {
                if (in_array($normalized, $variations, true)) {
                    $matched = $field;
                    break;
                }
            }

            $map[$header] = $matched;
        }

        return $map;
    }
}
