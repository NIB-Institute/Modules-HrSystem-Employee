<?php

use Illuminate\Support\Facades\Route;
use Modules\Employee\Http\Controllers\Api\V1\Employee\EmployeeAuthController;
use Modules\Employee\Http\Controllers\Api\V1\Employee\AttendanceController;
use Modules\Employee\Http\Controllers\Api\V1\Employee\PermissionRequestController;

/*
|--------------------------------------------------------------------------
| Public Routes (no authentication required)
|--------------------------------------------------------------------------
| These routes are for employee mobile app login
*/
Route::prefix('v1/employee')->group(function () {
    // Auth
    Route::post('auth/login', [EmployeeAuthController::class, 'login'])
        ->name('employee.auth.login');
});

/*
|--------------------------------------------------------------------------
| Protected Routes (requires authentication)
|--------------------------------------------------------------------------
| These routes require employee to be authenticated
*/
Route::middleware(['auth:sanctum'])->prefix('v1/employee')->group(function () {
    // Auth
    Route::post('auth/logout', [EmployeeAuthController::class, 'logout'])
        ->name('employee.auth.logout');
    Route::post('auth/logout-all', [EmployeeAuthController::class, 'logoutAll'])
        ->name('employee.auth.logout-all');
    Route::get('auth/me', [EmployeeAuthController::class, 'me'])
        ->name('employee.auth.me');
    Route::post('auth/update-profile', [EmployeeAuthController::class, 'updateProfile'])
        ->name('employee.auth.update-profile');

    // Attendance
    Route::get('attendance/today', [AttendanceController::class, 'today'])
        ->name('employee.attendance.today');
    Route::post('attendance/check-in', [AttendanceController::class, 'checkIn'])
        ->name('employee.attendance.check-in');
    Route::post('attendance/check-out', [AttendanceController::class, 'checkOut'])
        ->name('employee.attendance.check-out');
    Route::get('attendance/history', [AttendanceController::class, 'history'])
        ->name('employee.attendance.history');

    // Permission Requests
    Route::get('permission-requests/types', [PermissionRequestController::class, 'types'])
        ->name('employee.permission-requests.types');
    Route::get('permission-requests', [PermissionRequestController::class, 'index'])
        ->name('employee.permission-requests.index');
    Route::post('permission-requests', [PermissionRequestController::class, 'store'])
        ->name('employee.permission-requests.store');
    Route::get('permission-requests/{uuid}', [PermissionRequestController::class, 'show'])
        ->name('employee.permission-requests.show');
    Route::delete('permission-requests/{uuid}', [PermissionRequestController::class, 'cancel'])
        ->name('employee.permission-requests.cancel');
});
