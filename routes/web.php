<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ConsentDocumentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OvertimeApprovalController;
use App\Http\Controllers\OvertimeRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\CheckActiveUser;
use App\Http\Middleware\CheckMustChangePassword;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::middleware(['guest'])->group(function () {
    Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/', [LoginController::class, 'login']);
    Route::get('/login', [LoginController::class, 'showLoginForm']);
    Route::post('/login', [LoginController::class, 'login']);
});

// Authenticated Routes
Route::middleware(['auth', CheckActiveUser::class])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Force Password Change Routes
    Route::get('/change-password', [ProfileController::class, 'showChangePasswordForm'])->name('password.change');
    Route::post('/change-password', [ProfileController::class, 'updatePassword'])->name('password.update');

    // Protected Routes (Require MustChangePassword check)
    Route::middleware([CheckMustChangePassword::class])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/manual', function () { return view('manual'); })->name('manual');
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

        // OT Requests Routes (Phase 3 & 4)
        Route::resource('overtime', OvertimeRequestController::class);
        Route::post('overtime/{overtime}/submit', [OvertimeRequestController::class, 'submit'])->name('overtime.submit');
        Route::post('overtime/{overtime}/cancel', [OvertimeRequestController::class, 'cancel'])->name('overtime.cancel');

        // Consent Documents Routes (Phase 4)
        Route::get('overtime/{overtime}/pdf-consent', [ConsentDocumentController::class, 'downloadPdf'])->name('overtime.pdf-consent');
        Route::post('overtime/{overtime}/consent-status', [ConsentDocumentController::class, 'updateConsentStatus'])->name('overtime.consent-status');
        Route::post('overtime/{overtime}/upload-consent', [ConsentDocumentController::class, 'uploadSignedDocument'])->name('overtime.upload-consent');
        Route::get('consents/{consent}/download', [ConsentDocumentController::class, 'downloadFile'])->name('consents.download');

        // Manager Approval Routes (Phase 5)
        Route::middleware(['role:Manager|Super Admin'])->prefix('approvals')->name('approvals.')->group(function () {
            Route::get('/', [OvertimeApprovalController::class, 'index'])->name('index');
            Route::post('{overtime}/approve', [OvertimeApprovalController::class, 'approve'])->name('approve');
            Route::post('{overtime}/reject', [OvertimeApprovalController::class, 'reject'])->name('reject');
            Route::post('{overtime}/return', [OvertimeApprovalController::class, 'returnForRevision'])->name('return');
        });

        // Notifications Routes
        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

        // Actual Time Recording (Phase 7)
        Route::get('overtime/{overtime}/actual-time', [\App\Http\Controllers\ActualTimeController::class, 'edit'])->name('overtime.actual-time');
        Route::post('overtime/{overtime}/actual-time', [\App\Http\Controllers\ActualTimeController::class, 'update'])->name('overtime.actual-time.update');

        // Monthly Period Locks (Phase 7 - HR & Super Admin)
        Route::middleware(['role:HR|Super Admin'])->prefix('monthly-locks')->name('monthly-locks.')->group(function () {
            Route::get('/', [\App\Http\Controllers\MonthlyLockController::class, 'index'])->name('index');
            Route::post('toggle', [\App\Http\Controllers\MonthlyLockController::class, 'toggle'])->name('toggle');
        });

        // HIP Premium Time Integration Routes
        Route::middleware(['role:HR|Super Admin|Supervisor'])->prefix('hip')->name('hip.')->group(function () {
            Route::get('/', [\App\Http\Controllers\HipAttendanceController::class, 'index'])->name('index');
            Route::get('import', [\App\Http\Controllers\HipAttendanceController::class, 'create'])->name('create');
            Route::post('import', [\App\Http\Controllers\HipAttendanceController::class, 'store'])->name('store');
            Route::get('sample-template', [\App\Http\Controllers\HipAttendanceController::class, 'sampleTemplate'])->name('sample-template');
        });

        // Payroll Export & OT Payment Summary (HR & Super Admin)
        Route::middleware(['role:HR|Super Admin'])->prefix('payroll')->name('payroll.')->group(function () {
            Route::get('/', [\App\Http\Controllers\PayrollExportController::class, 'index'])->name('index');
            Route::match(['get', 'post'], 'export', [\App\Http\Controllers\PayrollExportController::class, 'export'])->name('export');
        });

        // Reports Routes (Phase 6)
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [\App\Http\Controllers\ReportController::class, 'index'])->name('index');
            Route::get('{type}', [\App\Http\Controllers\ReportController::class, 'show'])->name('show');
            Route::get('{type}/export-excel', [\App\Http\Controllers\ReportController::class, 'exportExcel'])->name('export-excel');
            Route::get('{type}/export-pdf', [\App\Http\Controllers\ReportController::class, 'exportPdf'])->name('export-pdf');
        });

        // Master Data & Audit Logs Routes (Admin & HR - Phase 8)
        Route::middleware(['role:Super Admin|HR'])->prefix('admin')->name('admin.')->group(function () {
            Route::get('audit-logs', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('audit-logs.index');

            Route::resource('users', UserController::class)->except(['destroy', 'show']);
            Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
            Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');

            Route::resource('departments', \App\Http\Controllers\Admin\DepartmentController::class);
            Route::resource('teams', \App\Http\Controllers\Admin\TeamController::class);
            Route::resource('positions', \App\Http\Controllers\Admin\PositionController::class);

            Route::get('employees/export', [\App\Http\Controllers\Admin\EmployeeController::class, 'export'])->name('employees.export');
            Route::get('employees/import', [\App\Http\Controllers\Admin\EmployeeController::class, 'showImportForm'])->name('employees.import-form');
            Route::post('employees/preview', [\App\Http\Controllers\Admin\EmployeeController::class, 'previewImport'])->name('employees.preview-import');
            Route::post('employees/confirm-import', [\App\Http\Controllers\Admin\EmployeeController::class, 'confirmImport'])->name('employees.confirm-import');
            Route::post('employees/clear-all', [\App\Http\Controllers\Admin\EmployeeController::class, 'clearAll'])->name('employees.clear-all');
            Route::get('employees/sample-template', [\App\Http\Controllers\Admin\EmployeeController::class, 'sampleTemplate'])->name('employees.sample-template');
            Route::resource('employees', \App\Http\Controllers\Admin\EmployeeController::class);

            Route::resource('overtime-types', \App\Http\Controllers\Admin\OvertimeTypeController::class);
            Route::resource('holidays', \App\Http\Controllers\Admin\HolidayController::class);
            Route::get('settings', [\App\Http\Controllers\Admin\SystemSettingController::class, 'index'])->name('settings.index');
            Route::post('settings', [\App\Http\Controllers\Admin\SystemSettingController::class, 'update'])->name('settings.update');
            Route::post('settings/remove-logo', [\App\Http\Controllers\Admin\SystemSettingController::class, 'removeLogo'])->name('settings.remove-logo');
        });
    });
});
