<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PayrollPeriodController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PayslipComponentController;
use App\Http\Controllers\TerRateController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\TimezoneController;
use App\Http\Controllers\EmailPreviewController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Auth Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->post('/timezone', [TimezoneController::class, 'store'])->name('timezone.store');

// Protected HRD area
Route::middleware(['auth', 'role:HRD', 'activity.log'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    Route::post('payroll-periods/calculate-row', [PayrollPeriodController::class, 'calculateRow'])->name('payroll-periods.calculate-row');
    Route::post('payroll-periods/{payroll_period}/save-draft', [PayrollPeriodController::class, 'saveDraft'])->name('payroll-periods.save-draft');
    Route::post('payroll-periods/{payroll_period}/import-template', [PayrollPeriodController::class, 'importTemplate'])->name('payroll-periods.import-template');
    Route::post('payroll-periods/{payroll_period}/publish-send', [PayrollPeriodController::class, 'publishAndSend'])->name('payroll-periods.publish-send');
    Route::post('payroll-periods/{payroll_period}/reopen-draft', [PayrollPeriodController::class, 'reopenDraft'])->name('payroll-periods.reopen-draft');
    Route::get('payroll-periods/{payroll_period}/download-template', [PayrollPeriodController::class, 'downloadTemplate'])->name('payroll-periods.download-template');
    Route::get('payroll-periods/{payroll_period}/preview-pdf', [PayrollPeriodController::class, 'previewPdf'])->name('payroll-periods.preview-pdf');
    Route::get('payroll-periods/data', [PayrollPeriodController::class, 'data'])->name('payroll-periods.data');
    Route::resource('payroll-periods', PayrollPeriodController::class);

    Route::get('attendances/data', [\App\Http\Controllers\AttendanceController::class, 'data'])->name('attendances.data');
    Route::post('attendances/import', [\App\Http\Controllers\AttendanceController::class, 'importExcel'])->name('attendances.import');
    Route::resource('attendances', \App\Http\Controllers\AttendanceController::class);

    Route::get('employees/data', [\App\Http\Controllers\EmployeeController::class, 'data'])->name('employees.data');
    Route::resource('employees', \App\Http\Controllers\EmployeeController::class);

    Route::get('payslip-components/data', [PayslipComponentController::class, 'data'])->name('payslip-components.data');
    Route::resource('payslip-components', PayslipComponentController::class);

    Route::get('ter-rates/data', [TerRateController::class, 'data'])->name('ter-rates.data');
    Route::resource('ter-rates', TerRateController::class);

    Route::get('email-previews/payslip', [EmailPreviewController::class, 'payslip'])->name('email-previews.payslip');

    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('/activity-logs/data', [ActivityLogController::class, 'data'])->name('activity-logs.data');
});
