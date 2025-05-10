<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\FiscalLedger;

class ReportsController extends Controller
{
    public function usersWithoutDepartments(Request $request)
    {
        $reportTitle = 'Users Without Departments';
        $reportDescription = 'This report lists all users who are not assigned to any department.';
        $search = $request->input('search');

        $sort = $request->input('sort', 'name'); // Default sort column
        $direction = $request->input('direction', 'asc'); // Default sort direction

        // Get the current fiscal ledger
        $currentLedger = FiscalLedger::where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        // Users with no department relationships
        $users = User::query()
            ->doesntHave('departments')
            ->where('active', 1)
            ->paginate(15);

        return view('reports.users', compact('users','sort','direction','search','reportTitle','reportDescription'));
    }

    public function usersWithoutHoursThisPeriod(request $request)
    {
        $reportTitle = 'Users Without Hours This Period';
        $reportDescription = 'This report lists all users who have not logged any hours in the current fiscal period.';

        $search = $request->input('search');

        $sort = $request->input('sort', 'name'); // Default sort column
        $direction = $request->input('direction', 'asc'); // Default sort direction

        $currentLedger = FiscalLedger::where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();

        if (!$currentLedger) {
            return back()->with('error', 'No current fiscal ledger found.');
        }

        $users = User::whereDoesntHave('volunteerHours', function ($query) use ($currentLedger) {
            $query->where('fiscal_ledger_id', $currentLedger->id);
        })->where('active', 1)
        ->paginate(15);

        return view('reports.users', compact('users', 'currentLedger', 'sort','direction','search','reportTitle','reportDescription'));
    }
}
