<?php

use Illuminate\Support\Facades\Route;
use Modules\Employee\Http\Controllers\Dashboard\V1\AttendanceController;
use Modules\Employee\Http\Controllers\Dashboard\V1\EmployeeController;
use Modules\Employee\Http\Controllers\Dashboard\V1\EmployeeTypeController;

// Routes moved to dashboard.php to avoid duplicate route names
// Route::middleware(['auth', 'verified'])->prefix('employee')->name('employee.')->group(function () {
//     Route::resource('employees', EmployeeController::class);
//     Route::resource('types', EmployeeTypeController::class)->names('types');
//     Route::resource('attendances', AttendanceController::class);
// });
