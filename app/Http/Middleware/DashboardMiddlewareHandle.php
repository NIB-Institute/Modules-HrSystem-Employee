<?php

namespace Modules\Employee\Http\Middleware;

use App\Services\MenuService;
use Closure;
use Illuminate\Http\Request;
use Modules\Employee\Enums\PermissionEnum;

/**
 * Registers the Employee module's primary sidebar items on every
 * dashboard request, before HandleInertiaRequests serializes the
 * menu tree for Inertia. Permissions reference PermissionEnum so
 * the strings stay in sync with RolesAndPermissionsSeeder.
 */
class DashboardMiddlewareHandle
{
    public function handle(Request $request, Closure $next)
    {
        MenuService::addMenuItem(
            menu: 'primary',
            id: 'employees',
            title: __('Employees'),
            url: route('employee.employees.index'),
            icon: 'Users',
            order: 50,
            permissions: PermissionEnum::VIEW_EMPLOYEES->value,
            route: 'employee.*',
        );

        MenuService::addSubmenuItem(
            'primary',
            'employees',
            __('All Employees'),
            route('employee.employees.index'),
            10,
            PermissionEnum::VIEW_EMPLOYEES->value,
            'employee.employees.*',
            'Users',
        );

        MenuService::addSubmenuItem(
            'primary',
            'employees',
            __('Employee Types'),
            route('employee.employee-types.index'),
            20,
            PermissionEnum::VIEW_EMPLOYEE_TYPES->value,
            'employee.employee-types.*',
            'Tags',
        );

        MenuService::addSubmenuItem(
            'primary',
            'employees',
            __('Attendance'),
            route('employee.attendances.index'),
            30,
            PermissionEnum::VIEW_ATTENDANCES->value,
            'employee.attendances.*',
            'ClipboardCheck',
        );

        MenuService::addSubmenuItem(
            'primary',
            'employees',
            __('QR Scanner'),
            route('employee.attendances.scanner'),
            40,
            PermissionEnum::SCAN_ATTENDANCE_QR->value,
            'employee.attendances.scanner',
            'QrCode',
        );

        MenuService::addSubmenuItem(
            'primary',
            'employees',
            __('Scan Locations'),
            route('employee.locations.index'),
            50,
            PermissionEnum::VIEW_LOCATIONS->value,
            'employee.locations.*',
            'MapPin',
        );

        MenuService::addSubmenuItem(
            'primary',
            'employees',
            __('Permission Requests'),
            route('employee.permission-requests.index'),
            60,
            PermissionEnum::VIEW_PERMISSION_REQUESTS->value,
            'employee.permission-requests.*',
            'FileText',
        );

        MenuService::addSubmenuItem(
            'primary',
            'employees',
            __('Self Service'),
            route('employee.attendances.self-service'),
            70,
            null,
            'employee.attendances.self-service',
            'LogIn',
        );

        return $next($request);
    }
}
