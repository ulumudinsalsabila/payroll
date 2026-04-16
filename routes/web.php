<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PayrollPeriodController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PayslipComponentController;
use App\Http\Controllers\TerRateController;
use App\Http\Controllers\ActivityLogController;

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

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('payroll-periods.index');
    }
    return redirect()->route('login');
});

// Auth Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected HRD area
Route::middleware(['auth', 'role:HRD', 'activity.log'])->group(function () {
    Route::post('payroll-periods/calculate-row', [PayrollPeriodController::class, 'calculateRow'])->name('payroll-periods.calculate-row');
    Route::post('payroll-periods/{payroll_period}/save-draft', [PayrollPeriodController::class, 'saveDraft'])->name('payroll-periods.save-draft');
    Route::post('payroll-periods/{payroll_period}/import-template', [PayrollPeriodController::class, 'importTemplate'])->name('payroll-periods.import-template');
    Route::post('payroll-periods/{payroll_period}/publish-send', [PayrollPeriodController::class, 'publishAndSend'])->name('payroll-periods.publish-send');
    Route::get('payroll-periods/{payroll_period}/download-template', [PayrollPeriodController::class, 'downloadTemplate'])->name('payroll-periods.download-template');
    Route::get('payroll-periods/{payroll_period}/preview-pdf', [PayrollPeriodController::class, 'previewPdf'])->name('payroll-periods.preview-pdf');
    Route::resource('payroll-periods', PayrollPeriodController::class);
    Route::resource('employees', \App\Http\Controllers\EmployeeController::class);
    Route::resource('payslip-components', PayslipComponentController::class);
    Route::resource('ter-rates', TerRateController::class);
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
});
