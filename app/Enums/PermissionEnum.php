<?php

namespace Modules\Employee\Enums;

/**
 * Single source of truth for Employee module permission strings.
 *
 * Use ::value (e.g. PermissionEnum::VIEW_EMPLOYEES->value) wherever
 * Spatie permission names are required - middleware, route guards,
 * MenuService registrations, etc. The values must match the
 * permissions seeded by RolesAndPermissionsSeeder.
 */
enum PermissionEnum: string
{
    case VIEW_EMPLOYEES = 'employees.view_any';
    case VIEW_EMPLOYEE_TYPES = 'employee_types.view_any';
    case VIEW_ATTENDANCES = 'attendances.view_any';
    case SCAN_ATTENDANCE_QR = 'attendances.scan_qr';
    case VIEW_LOCATIONS = 'locations.view_any';
    case VIEW_PERMISSION_REQUESTS = 'permission_requests.view_any';
}
