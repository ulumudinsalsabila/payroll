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
    Route::resource('payroll-periods', PayrollPeriodController::class);
    Route::resource('employees', \App\Http\Controllers\EmployeeController::class);
    Route::resource('payslip-components', PayslipComponentController::class);
    Route::resource('ter-rates', TerRateController::class);
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
});
