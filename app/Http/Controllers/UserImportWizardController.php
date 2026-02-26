<?php

namespace App\Http\Controllers;

use App\Models\CustomField;
use App\Models\CustomFieldValue;
use App\Models\Department;
use App\Models\FiscalLedger;
use App\Models\User;
use App\Models\VolunteerHours;
use Carbon\Carbon;
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
            'wizard_import_hours_config',
            'wizard_import_results',
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

        return redirect()->route('users.import.hours');
    }

    // ─── Step 3 ──────────────────────────────────────────────────────────────

    /**
     * Show the hours configuration form (Step 3).
     */
    public function hoursConfig()
    {
        if (!session('wizard_import_headers') || !session('wizard_import_mapping')) {
            return redirect()->route('users.import');
        }

        $headers       = session('wizard_import_headers');
        $ledgers       = FiscalLedger::orderBy('start_date', 'desc')->get();
        $currentConfig = session('wizard_import_hours_config', []);

        return view('users.import.step3_hours', compact('headers', 'ledgers', 'currentConfig'));
    }

    /**
     * Store the hours configuration and redirect to the confirmation step.
     */
    public function storeHoursConfig(Request $request)
    {
        $skip = $request->boolean('skip_hours');

        if (!$skip) {
            $request->validate([
                'hours_column'  => 'required|string',
                'hours_description' => 'nullable|string|max:255',
            ]);
        }

        $config = [
            'skip'         => $skip,
            'hours_column' => $skip ? null : $request->input('hours_column'),
            'description'  => $request->input('hours_description', 'Imported volunteer hours'),
            'ledger_id'    => $request->input('ledger_id') ?: null,
        ];

        session(['wizard_import_hours_config' => $config]);

        return redirect()->route('users.import.confirm');
    }

    // ─── Step 4 ──────────────────────────────────────────────────────────────

    /**
     * Show the import confirmation / preview (Step 4).
     */
    public function confirm()
    {
        if (!session('wizard_import_headers') || !session('wizard_import_mapping') || !session()->has('wizard_import_hours_config')) {
            return redirect()->route('users.import');
        }

        $headers      = session('wizard_import_headers');
        $mapping      = session('wizard_import_mapping');
        $path         = session('wizard_import_path');
        $total        = session('wizard_import_total', 0);
        $customFields = CustomField::active()->ordered()->get();
        $appFields    = $this->buildAppFields($customFields);
        $hoursConfig  = session('wizard_import_hours_config', []);

        // Resolve ledger name for display
        $ledgerName = null;
        if (!empty($hoursConfig['ledger_id'])) {
            $ledger = FiscalLedger::find($hoursConfig['ledger_id']);
            $ledgerName = $ledger?->name;
        }

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

            // Append hours preview value if configured
            if (!empty($hoursConfig['hours_column'])) {
                $resolved['_hours_import'] = $raw[$hoursConfig['hours_column']] ?? '';
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
            'mapping', 'appFields', 'usedFields', 'previewRows', 'total',
            'hoursConfig', 'ledgerName'
        ));
    }

    // ─── Execute ─────────────────────────────────────────────────────────────

    /**
     * Run the import and redirect to the user index.
     */
    public function execute(Request $request)
    {
        $mapping     = session('wizard_import_mapping', []);
        $path        = session('wizard_import_path');
        $headers     = session('wizard_import_headers');
        $hoursConfig = session('wizard_import_hours_config', []);

        if (!$path || !$headers) {
            return redirect()->route('users.import');
        }

        $customFields = CustomField::active()->ordered()->get()->keyBy('field_key');
        $departments  = Department::all()->keyBy(fn($d) => strtolower($d->name));

        $importHours   = !empty($hoursConfig) && !($hoursConfig['skip'] ?? true) && !empty($hoursConfig['hours_column']);
        $hoursLedgerId = $hoursConfig['ledger_id'] ?? null;
        $hoursDesc     = $hoursConfig['description'] ?? 'Imported volunteer hours';
        $hoursColumn   = $hoursConfig['hours_column'] ?? null;

        $fullPath = Storage::disk('local')->path($path);
        $handle   = fopen($fullPath, 'r');
        fgetcsv($handle); // skip the header row

        $created     = 0;
        $skipped     = 0;
        $failed      = 0;
        $failedRows  = [];
        $skippedRows = [];
        $rowNum      = 1;

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
                } elseif ($field === 'created_at') {
                    // Parse date — support MM-DD-YYYY as well as standard formats
                    $parsed = null;
                    if ($value !== '') {
                        try {
                            // Try MM-DD-YYYY first (e.g. 04-03-2025)
                            $parsed = Carbon::createFromFormat('m-d-Y', $value);
                        } catch (\Exception $e) {
                            try {
                                $parsed = Carbon::parse($value);
                            } catch (\Exception $e2) {
                                // Leave null — will fall back to creation timestamp
                            }
                        }
                    }
                    $userData['_created_at'] = $parsed;
                } else {
                    $userData[$field] = $value;
                }
            }

            // Email is required
            if (empty($userData['email']) || !filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
                $failedRows[] = [
                    'row'    => $rowNum,
                    'email'  => $userData['email'] ?? '',
                    'name'   => $this->resolveNameFromData($userData),
                    'reason' => 'Invalid or missing email address',
                    'raw'    => $raw,
                ];
                $failed++;
                continue;
            }

            // Skip users that already exist
            if (User::where('email', $userData['email'])->exists()) {
                $skippedRows[] = [
                    'row'   => $rowNum,
                    'email' => $userData['email'],
                    'name'  => $this->resolveNameFromData($userData),
                ];
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

            $deptId    = $userData['_department_id'] ?? null;
            $createdAt = $userData['_created_at'] ?? null;
            unset($userData['_department_id'], $userData['_created_at']);

            try {
                $user = User::create($userData);

                // Backdate created_at if a value was mapped
                if ($createdAt instanceof Carbon) {
                    $user->timestamps = false;
                    $user->created_at = $createdAt;
                    $user->save();
                    $user->timestamps = true;
                }

                if ($deptId) {
                    $user->departments()->attach($deptId);
                }

                foreach ($customFieldData as $fieldKey => $value) {
                    if (!$customFields->has($fieldKey)) {
                        continue;
                    }

                    // Normalize date-type custom fields to Y-m-d so Carbon::parse() can read them
                    if ($customFields[$fieldKey]->field_type === 'date' && $value !== '') {
                        $normalizedDate = null;
                        try {
                            // Try MM-DD-YYYY (e.g. 01-31-1979) first
                            $normalizedDate = Carbon::createFromFormat('m-d-Y', $value)->format('Y-m-d');
                        } catch (\Exception $e) {
                            try {
                                $normalizedDate = Carbon::parse($value)->format('Y-m-d');
                            } catch (\Exception $e2) {
                                // Leave the original value if it can't be parsed
                                $normalizedDate = $value;
                            }
                        }
                        $value = $normalizedDate;
                    }

                    CustomFieldValue::updateOrCreate(
                        ['user_id' => $user->id, 'custom_field_id' => $customFields[$fieldKey]->id],
                        ['value' => $value]
                    );
                }

                // Create a VolunteerHours record if hours import is configured
                if ($importHours && $hoursColumn !== null) {
                    $hoursValue = $raw[$hoursColumn] ?? '';
                    $hoursValue = is_numeric(trim($hoursValue)) ? (float) trim($hoursValue) : null;

                    if ($hoursValue !== null && $hoursValue > 0) {
                        VolunteerHours::create([
                            'user_id'          => $user->id,
                            'hours'            => $hoursValue,
                            'description'      => $hoursDesc,
                            'fiscal_ledger_id' => $hoursLedgerId,
                            'volunteer_date'   => now()->toDateString(),
                        ]);
                    }
                }

                $created++;
            } catch (\Exception $e) {
                $failedRows[] = [
                    'row'    => $rowNum,
                    'email'  => $userData['email'] ?? '',
                    'name'   => $this->resolveNameFromData($userData),
                    'reason' => $e->getMessage(),
                    'raw'    => $raw,
                ];
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
            'wizard_import_hours_config',
        ]);

        session(['wizard_import_results' => [
            'created'      => $created,
            'skipped'      => $skipped,
            'failed'       => $failed,
            'total'        => $rowNum - 1,
            'failed_rows'  => $failedRows,
            'skipped_rows' => $skippedRows,
            'headers'      => $headers,
            'imported_at'  => now()->toDateTimeString(),
        ]]);

        return redirect()->route('users.import.results');
    }

    // ─── Results ─────────────────────────────────────────────────────────────

    /**
     * Show the import results page.
     */
    public function results()
    {
        $results = session('wizard_import_results');

        if (!$results) {
            return redirect()->route('users.import');
        }

        return view('users.import.results', compact('results'));
    }

    /**
     * Download failed rows as a CSV.
     */
    public function downloadFailed()
    {
        $results = session('wizard_import_results');

        if (!$results || empty($results['failed_rows'])) {
            return redirect()->route('users.import.results');
        }

        $headers     = $results['headers'] ?? [];
        $failedRows  = $results['failed_rows'];
        $filename    = 'import-failed-' . now()->format('Y-m-d-His') . '.csv';

        $callback = function () use ($headers, $failedRows) {
            $handle = fopen('php://output', 'w');

            // Header row: original columns + a "Failure Reason" column
            fputcsv($handle, array_merge(['Row #'], $headers, ['Failure Reason']));

            foreach ($failedRows as $failure) {
                $rowData = [];
                foreach ($headers as $header) {
                    $rowData[] = $failure['raw'][$header] ?? '';
                }
                fputcsv($handle, array_merge([$failure['row']], $rowData, [$failure['reason']]));
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
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
            'created_at' => 'Created At (MM-DD-YYYY or YYYY-MM-DD)',
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
            'created_at' => ['created_at', 'created', 'joined', 'joined_at', 'join_date', 'registration_date', 'date joined', 'signup_date', 'signup date', 'date'],
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

    /**
     * Resolve a display name from partially-parsed user data.
     */
    protected function resolveNameFromData(array $userData): string
    {
        if (!empty($userData['name'])) {
            return $userData['name'];
        }

        $parts = array_filter([
            $userData['first_name'] ?? '',
            $userData['last_name']  ?? '',
        ]);

        return implode(' ', $parts);
    }
}
