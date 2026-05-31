<?php

use Illuminate\Support\Facades\Route;
use Modules\Employee\Http\Controllers\Dashboard\V1\EmployeeController;
use Modules\Employee\Http\Controllers\Dashboard\V1\EmployeeImportExportController;
use Modules\Employee\Http\Controllers\Dashboard\V1\EmployeePasswordController;
use Modules\Employee\Http\Controllers\Dashboard\V1\EmployeeTypeController;
use Modules\Employee\Http\Controllers\Dashboard\V1\AttendanceController;
use Modules\Employee\Http\Controllers\Dashboard\V1\LocationController;
// use Modules\Employee\Http\Controllers\Dashboard\V1\EmployeeExperienceController;
use Modules\Employee\Http\Controllers\Dashboard\V1\PermissionRequestController;
use Modules\Employee\Http\Controllers\Dashboard\V1\EmployeePlanController;
use Modules\Employee\Http\Controllers\Dashboard\V1\EmployeePlanAssignmentController;
use Modules\Employee\Http\Controllers\Dashboard\V1\DocumentController;
use Modules\Employee\Http\Controllers\Dashboard\V1\EmployeeAvailabilityController;
use Modules\Employee\Http\Controllers\Dashboard\V1\EmployeeTrashController;
use Modules\Employee\Http\Controllers\Dashboard\V1\EmployeeTypeTrashController;
use Modules\Employee\Http\Controllers\Dashboard\V1\AttendanceTrashController;
use Modules\Employee\Http\Controllers\Dashboard\V1\SelfServiceAttendanceController;

/*
|--------------------------------------------------------------------------
| Employee Module Dashboard Routes
|--------------------------------------------------------------------------
|
| Using 'auto.permission' middleware which automatically resolves permissions
| from route names. Route naming pattern: {resource}.{action}
|
| Permission mapping:
| - index -> view_any
| - create/store -> create
| - show -> view
| - edit/update -> update
| - destroy/delete -> delete
|
| For non-standard actions, use explicit permission middleware.
|
*/

Route::middleware(['auth', 'verified', 'auto.permission'])->prefix('dashboard')->name('employee.')->group(function () {

    // ==================== TRASH ROUTES ====================

    // Employees Trash
    Route::get('employees/trash', [EmployeeTrashController::class, 'index'])->name('employees.trash.index');
    Route::put('employees/{uuid}/restore', [EmployeeTrashController::class, 'restore'])->name('employees.trash.restore');
    Route::delete('employees/{uuid}/force-delete', [EmployeeTrashController::class, 'forceDelete'])->name('employees.trash.force-delete');
    Route::delete('employees/trash/empty', [EmployeeTrashController::class, 'empty'])->name('employees.trash.empty');
    Route::put('employees/trash/bulk-restore', [EmployeeTrashController::class, 'bulkRestore'])->name('employees.trash.bulk-restore');
    Route::delete('employees/trash/bulk-force-delete', [EmployeeTrashController::class, 'bulkForceDelete'])->name('employees.trash.bulk-force-delete');

    // Employee Types Trash
    Route::get('employee-types/trash', [EmployeeTypeTrashController::class, 'index'])->name('employee-types.trash.index');
    Route::put('employee-types/{uuid}/restore', [EmployeeTypeTrashController::class, 'restore'])->name('employee-types.trash.restore');
    Route::delete('employee-types/{uuid}/force-delete', [EmployeeTypeTrashController::class, 'forceDelete'])->name('employee-types.trash.force-delete');
    Route::delete('employee-types/trash/empty', [EmployeeTypeTrashController::class, 'empty'])->name('employee-types.trash.empty');
    Route::put('employee-types/trash/bulk-restore', [EmployeeTypeTrashController::class, 'bulkRestore'])->name('employee-types.trash.bulk-restore');
    Route::delete('employee-types/trash/bulk-force-delete', [EmployeeTypeTrashController::class, 'bulkForceDelete'])->name('employee-types.trash.bulk-force-delete');

    // Attendances Trash
    Route::get('attendances/trash', [AttendanceTrashController::class, 'index'])->name('attendances.trash.index');
    Route::put('attendances/{uuid}/restore', [AttendanceTrashController::class, 'restore'])->name('attendances.trash.restore');
    Route::delete('attendances/{uuid}/force-delete', [AttendanceTrashController::class, 'forceDelete'])->name('attendances.trash.force-delete');
    Route::delete('attendances/trash/empty', [AttendanceTrashController::class, 'empty'])->name('attendances.trash.empty');
    Route::put('attendances/trash/bulk-restore', [AttendanceTrashController::class, 'bulkRestore'])->name('attendances.trash.bulk-restore');
    Route::delete('attendances/trash/bulk-force-delete', [AttendanceTrashController::class, 'bulkForceDelete'])->name('attendances.trash.bulk-force-delete');

    // ==================== CRUD ROUTES ====================

    // Departments AJAX endpoint
    Route::get('employees/departments', [EmployeeController::class, 'getDepartments'])->name('employees.departments');

    // Export/Import routes (before parameterized routes)
    Route::get('employees/export-options', [EmployeeImportExportController::class, 'exportOptions'])->name('employees.export-options');
    Route::get('employees/export', [EmployeeImportExportController::class, 'export'])->name('employees.export');
    Route::get('employees/import', [EmployeeImportExportController::class, 'showImport'])->name('employees.import');
    Route::post('employees/import', [EmployeeImportExportController::class, 'import'])->name('employees.import.store');
    Route::post('employees/import/preview', [EmployeeImportExportController::class, 'preview'])->name('employees.import.preview');
    Route::get('employees/import/failed', [EmployeeImportExportController::class, 'downloadFailedRows'])->name('employees.import.failed');
    Route::get('employees/template', [EmployeeImportExportController::class, 'downloadTemplate'])->name('employees.template');

    // Employees - CREATE routes first (before parameterized routes)
    Route::get('employees/bulk-delete', [EmployeeController::class, 'confirmBulkDelete'])->name('employees.bulk-delete.confirm');
    Route::delete('employees/bulk-delete', [EmployeeController::class, 'bulkDelete'])->name('employees.bulk-delete');
    Route::get('employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::get('employees/{employee}/qr-badge', [EmployeeController::class, 'qrCode'])->name('employees.qr-badge');
    Route::get('employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::patch('employees/{employee}', [EmployeeController::class, 'update']);
    Route::put('employees/{employee}/toggle-status', [EmployeeController::class, 'toggleStatus'])->name('employees.toggle-status');
    Route::post('employees/{employee}/regenerate-qr', [EmployeeController::class, 'regenerateQrCode'])->name('employees.regenerate-qr');
    Route::get('employees/{employee}/delete', [EmployeeController::class, 'confirmDelete'])->name('employees.confirm-delete');
    Route::delete('employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

    // Employee Password & Account Management (separate controller)
    Route::get('employees/{employee}/change-password', [EmployeePasswordController::class, 'edit'])->name('employees.change-password');
    Route::put('employees/{employee}/change-password', [EmployeePasswordController::class, 'update'])->name('employees.update-password');
    Route::get('employees/{employee}/create-account', [EmployeePasswordController::class, 'showCreateAccount'])->name('employees.show-create-account');
    Route::post('employees/{employee}/create-account', [EmployeePasswordController::class, 'createAccount'])->name('employees.create-account');

    // Employee Types - CREATE routes first
    Route::get('employee-types/bulk-delete', [EmployeeTypeController::class, 'confirmBulkDelete'])->name('employee-types.bulk-delete.confirm');
    Route::delete('employee-types/bulk-delete', [EmployeeTypeController::class, 'bulkDelete'])->name('employee-types.bulk-delete');
    Route::get('employee-types/create', [EmployeeTypeController::class, 'create'])->name('employee-types.create');
    Route::post('employee-types', [EmployeeTypeController::class, 'store'])->name('employee-types.store');
    Route::get('employee-types', [EmployeeTypeController::class, 'index'])->name('employee-types.index');
    Route::get('employee-types/{employee_type}', [EmployeeTypeController::class, 'show'])->name('employee-types.show');
    Route::get('employee-types/{employee_type}/edit', [EmployeeTypeController::class, 'edit'])->name('employee-types.edit');
    Route::put('employee-types/{employee_type}', [EmployeeTypeController::class, 'update'])->name('employee-types.update');
    Route::patch('employee-types/{employee_type}', [EmployeeTypeController::class, 'update']);
    Route::put('employee-types/{employee_type}/toggle-status', [EmployeeTypeController::class, 'toggleStatus'])->name('employee-types.toggle-status');
    Route::get('employee-types/{employee_type}/delete', [EmployeeTypeController::class, 'confirmDelete'])->name('employee-types.confirm-delete');
    Route::delete('employee-types/{employee_type}', [EmployeeTypeController::class, 'destroy'])->name('employee-types.destroy');

    // Documents (file library) - CREATE routes first
    Route::get('documents/create', [DocumentController::class, 'create'])->name('documents.create');
    Route::post('documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('documents/{document}/edit', [DocumentController::class, 'edit'])->name('documents.edit');
    Route::put('documents/{document}', [DocumentController::class, 'update'])->name('documents.update');
    Route::post('documents/{document}', [DocumentController::class, 'update']); // POST fallback for multipart updates
    Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::get('documents/{document}/delete', [DocumentController::class, 'confirmDelete'])->name('documents.confirm-delete');
    Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    // Locations CRUD (for geofence management)
    Route::get('locations/create', [LocationController::class, 'create'])->name('locations.create');
    Route::post('locations', [LocationController::class, 'store'])->name('locations.store');
    Route::get('locations', [LocationController::class, 'index'])->name('locations.index');
    Route::get('locations/{location}', [LocationController::class, 'show'])->name('locations.show');
    Route::get('locations/{location}/edit', [LocationController::class, 'edit'])->name('locations.edit');
    Route::put('locations/{location}', [LocationController::class, 'update'])->name('locations.update');
    Route::put('locations/{location}/schedule', [LocationController::class, 'updateSchedule'])->name('locations.update-schedule');
    Route::put('locations/{location}/toggle-status', [LocationController::class, 'toggleStatus'])->name('locations.toggle-status');
    Route::delete('locations/{location}', [LocationController::class, 'destroy'])->name('locations.destroy');

    // Attendance - Self-Service (for employees to check-in/out themselves)
    Route::get('attendances/self-service', [SelfServiceAttendanceController::class, 'index'])->name('attendances.self-service');
    Route::post('attendances/self-service/check-in', [SelfServiceAttendanceController::class, 'checkIn'])->name('attendances.self-service.check-in');
    Route::post('attendances/self-service/check-out', [SelfServiceAttendanceController::class, 'checkOut'])->name('attendances.self-service.check-out');

    // Attendance - Scanner (special permission: scan_qr)
    Route::middleware('permission:attendances.scan_qr')->group(function () {
        Route::get('attendances/scanner', [AttendanceController::class, 'scanner'])->name('attendances.scanner');
        Route::post('attendances/scan', [AttendanceController::class, 'processScan'])->name('attendances.scan');
        Route::get('attendances/today-summary', [AttendanceController::class, 'todaySummary'])->name('attendances.today-summary');
    });

    // Attendance Analytics
    Route::get('attendances/analytics', [AttendanceController::class, 'analytics'])->name('attendances.analytics');

    // Attendance CRUD - CREATE routes first
    Route::get('attendances/bulk-delete', [AttendanceController::class, 'confirmBulkDelete'])->name('attendances.bulk-delete.confirm');
    Route::delete('attendances/bulk-delete', [AttendanceController::class, 'bulkDelete'])->name('attendances.bulk-delete');
    Route::get('attendances/create', [AttendanceController::class, 'create'])->name('attendances.create');
    Route::post('attendances', [AttendanceController::class, 'store'])->name('attendances.store');
    Route::get('attendances', [AttendanceController::class, 'index'])->name('attendances.index');
    Route::get('attendances/{attendance}', [AttendanceController::class, 'show'])->name('attendances.show');
    Route::get('attendances/{attendance}/edit', [AttendanceController::class, 'edit'])->name('attendances.edit');
    Route::put('attendances/{attendance}', [AttendanceController::class, 'update'])->name('attendances.update');
    Route::patch('attendances/{attendance}', [AttendanceController::class, 'update']);
    Route::delete('attendances/{attendance}', [AttendanceController::class, 'destroy'])->name('attendances.destroy');

    // Employee Experiences CRUD (TODO: Create EmployeeExperienceController)
    // Route::get('experiences/create', [EmployeeExperienceController::class, 'create'])->name('experiences.create');
    // Route::post('experiences', [EmployeeExperienceController::class, 'store'])->name('experiences.store');
    // Route::get('experiences', [EmployeeExperienceController::class, 'index'])->name('experiences.index');
    // Route::get('experiences/{experience}', [EmployeeExperienceController::class, 'show'])->name('experiences.show');
    // Route::get('experiences/{experience}/edit', [EmployeeExperienceController::class, 'edit'])->name('experiences.edit');
    // Route::put('experiences/{experience}', [EmployeeExperienceController::class, 'update'])->name('experiences.update');
    // Route::get('experiences/{experience}/delete', [EmployeeExperienceController::class, 'confirmDelete'])->name('experiences.confirm-delete');
    // Route::delete('experiences/{experience}', [EmployeeExperienceController::class, 'destroy'])->name('experiences.destroy');

    // Permission Requests CRUD
    Route::get('permission-requests/create', [PermissionRequestController::class, 'create'])->name('permission-requests.create');
    Route::post('permission-requests', [PermissionRequestController::class, 'store'])->name('permission-requests.store');
    Route::get('permission-requests', [PermissionRequestController::class, 'index'])->name('permission-requests.index');
    Route::get('permission-requests/{permission_request}', [PermissionRequestController::class, 'show'])->name('permission-requests.show');
    Route::get('permission-requests/{permission_request}/edit', [PermissionRequestController::class, 'edit'])->name('permission-requests.edit');
    Route::put('permission-requests/{permission_request}', [PermissionRequestController::class, 'update'])->name('permission-requests.update');
    Route::get('permission-requests/{permission_request}/delete', [PermissionRequestController::class, 'confirmDelete'])->name('permission-requests.confirm-delete');
    Route::delete('permission-requests/{permission_request}', [PermissionRequestController::class, 'destroy'])->name('permission-requests.destroy');
    Route::get('permission-requests/{permission_request}/review', [PermissionRequestController::class, 'showReview'])->name('permission-requests.review');
    Route::post('permission-requests/{permission_request}/review', [PermissionRequestController::class, 'review'])->name('permission-requests.review.submit');

    // Employee Plans CRUD
    Route::get('employee-plans/create', [EmployeePlanController::class, 'create'])->name('employee-plans.create');
    Route::post('employee-plans', [EmployeePlanController::class, 'store'])->name('employee-plans.store');
    Route::get('employee-plans', [EmployeePlanController::class, 'index'])->name('employee-plans.index');
    Route::get('employee-plans/{employeePlan}/edit', [EmployeePlanController::class, 'edit'])->name('employee-plans.edit');
    Route::get('employee-plans/{employeePlan}', [EmployeePlanController::class, 'show'])->name('employee-plans.show');
    Route::put('employee-plans/{employeePlan}', [EmployeePlanController::class, 'update'])->name('employee-plans.update');
    Route::patch('employee-plans/{employeePlan}', [EmployeePlanController::class, 'update']);
    Route::get('employee-plans/{employeePlan}/delete', [EmployeePlanController::class, 'confirmDelete'])->name('employee-plans.confirm-delete');
    Route::delete('employee-plans/{employeePlan}', [EmployeePlanController::class, 'destroy'])->name('employee-plans.destroy');

    // Employee Plan Assignments CRUD
    Route::get('employee-plan-assignments/create', [EmployeePlanAssignmentController::class, 'create'])->name('employee-plan-assignments.create');
    Route::post('employee-plan-assignments', [EmployeePlanAssignmentController::class, 'store'])->name('employee-plan-assignments.store');
    Route::post('employee-plan-assignments/bulk-assign', [EmployeePlanAssignmentController::class, 'bulkAssign'])->name('employee-plan-assignments.bulk-assign');
    Route::get('employee-plan-assignments', [EmployeePlanAssignmentController::class, 'index'])->name('employee-plan-assignments.index');
    Route::get('employee-plan-assignments/{employeePlanAssignment}/edit', [EmployeePlanAssignmentController::class, 'edit'])->name('employee-plan-assignments.edit');
    Route::get('employee-plan-assignments/{employeePlanAssignment}', [EmployeePlanAssignmentController::class, 'show'])->name('employee-plan-assignments.show');
    Route::put('employee-plan-assignments/{employeePlanAssignment}', [EmployeePlanAssignmentController::class, 'update'])->name('employee-plan-assignments.update');
    Route::patch('employee-plan-assignments/{employeePlanAssignment}', [EmployeePlanAssignmentController::class, 'update']);
    Route::get('employee-plan-assignments/{employeePlanAssignment}/delete', [EmployeePlanAssignmentController::class, 'confirmDelete'])->name('employee-plan-assignments.confirm-delete');
    Route::delete('employee-plan-assignments/{employeePlanAssignment}', [EmployeePlanAssignmentController::class, 'destroy'])->name('employee-plan-assignments.destroy');

    // Employee Availabilities CRUD
    Route::get('employee-availabilities/create', [EmployeeAvailabilityController::class, 'create'])->name('employee-availabilities.create');
    Route::post('employee-availabilities', [EmployeeAvailabilityController::class, 'store'])->name('employee-availabilities.store');
    Route::get('employee-availabilities', [EmployeeAvailabilityController::class, 'index'])->name('employee-availabilities.index');
    Route::get('employee-availabilities/{employeeAvailability}/edit', [EmployeeAvailabilityController::class, 'edit'])->name('employee-availabilities.edit');
    Route::put('employee-availabilities/{employeeAvailability}', [EmployeeAvailabilityController::class, 'update'])->name('employee-availabilities.update');
    Route::patch('employee-availabilities/{employeeAvailability}', [EmployeeAvailabilityController::class, 'update']);
    Route::get('employee-availabilities/{employeeAvailability}/delete', [EmployeeAvailabilityController::class, 'confirmDelete'])->name('employee-availabilities.confirm-delete');
    Route::delete('employee-availabilities/{employeeAvailability}', [EmployeeAvailabilityController::class, 'destroy'])->name('employee-availabilities.destroy');

    // QR Code Generation
    Route::get('employees/{employee}/qr-code', [AttendanceController::class, 'generateEmployeeQr'])->name('employees.qr-code');
    Route::get('departments/{department}/qr-code', [AttendanceController::class, 'generateDepartmentQr'])->name('departments.qr-code');
    Route::get('classrooms/{classroom}/qr-code', [AttendanceController::class, 'generateClassroomQr'])->name('classrooms.qr-code');
});
