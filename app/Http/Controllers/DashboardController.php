<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\OvertimeType;
use App\Services\DashboardAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the application dashboard with Daily, Monthly, Yearly and Custom period analytics.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $filters = $request->only([
            'period_type',
            'date',
            'month',
            'year',
            'start_date',
            'end_date',
            'department_id',
            'overtime_type_id',
            'status',
        ]);

        $dashboardData = DashboardAnalyticsService::getDashboardData($user, $filters);

        $departments = Department::all();
        $overtimeTypes = OvertimeType::all();

        return view('dashboard', array_merge([
            'user' => $user,
            'filters' => $filters,
            'departments' => $departments,
            'overtimeTypes' => $overtimeTypes,
        ], $dashboardData));
    }
}
