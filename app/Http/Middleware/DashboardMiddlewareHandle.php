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
            permissions: PermissionEnum::EMPLOYEES_VIEW_ANY->value,
            route: 'employee.*',
        );

        MenuService::addSubmenuItem(
            'primary',
            'employees',
            __('All Employees'),
            route('employee.employees.index'),
            10,
            PermissionEnum::EMPLOYEES_VIEW_ANY->value,
            'employee.employees.*',
            'Users',
        );

        MenuService::addSubmenuItem(
            'primary',
            'employees',
            __('Employee Types'),
            route('employee.employee-types.index'),
            20,
            PermissionEnum::EMPLOYEE_TYPES_VIEW_ANY->value,
            'employee.employee-types.*',
            'Tags',
        );

        MenuService::addSubmenuItem(
            'primary',
            'employees',
            __('Employee Plans'),
            route('employee.employee-plans.index'),
            30,
            PermissionEnum::EMPLOYEE_PLANS_VIEW_ANY->value,
            'employee.employee-plans.*',
            'NotebookPen',
        );

        MenuService::addSubmenuItem(
            'primary',
            'employees',
            __('Plan Assignments'),
            route('employee.employee-plan-assignments.index'),
            40,
            PermissionEnum::EMPLOYEE_PLAN_ASSIGNMENTS_VIEW_ANY->value,
            'employee.employee-plan-assignments.*',
            'UsersRound',
        );

        MenuService::addSubmenuItem(
            'primary',
            'employees',
            __('Availability'),
            route('employee.employee-availabilities.index'),
            50,
            PermissionEnum::EMPLOYEE_AVAILABILITIES_VIEW_ANY->value,
            'employee.employee-availabilities.*',
            'CalendarRange',
        );

        MenuService::addSubmenuItem(
            'primary',
            'employees',
            __('Documents'),
            route('employee.documents.index'),
            60,
            PermissionEnum::EMPLOYEE_DOCUMENTS_VIEW_ANY->value,
            'employee.documents.*',
            'FileText',
        );

        /**
         * just uncommenting these for now, 
         * you will use the other fearture menu on the sidebar back
         */
        // MenuService::addSubmenuItem(
        //     'primary',
        //     'employees',
        //     __('Self Service'),
        //     route('employee.attendances.self-service'),
        //     60,
        //     null,
        //     'employee.attendances.self-service',
        //     'LogIn',
        // );

        // MenuService::addSubmenuItem(
        //     'primary',
        //     'employees',
        //     __('Attendance'),
        //     route('employee.attendances.index'),
        //     70,
        //     PermissionEnum::ATTENDANCES_VIEW_ANY->value,
        //     'employee.attendances.*',
        //     'ClipboardCheck',
        // );

        // MenuService::addSubmenuItem(
        //     'primary',
        //     'employees',
        //     __('QR Scanner'),
        //     route('employee.attendances.scanner'),
        //     80,
        //     PermissionEnum::ATTENDANCES_SCAN_QR->value,
        //     'employee.attendances.scanner',
        //     'QrCode',
        // );

        // MenuService::addSubmenuItem(
        //     'primary',
        //     'employees',
        //     __('Scan Locations'),
        //     route('employee.locations.index'),
        //     90,
        //     PermissionEnum::LOCATIONS_VIEW_ANY->value,
        //     'employee.locations.*',
        //     'MapPin',
        // );

        // MenuService::addSubmenuItem(
        //     'primary',
        //     'employees',
        //     __('Permission Requests'),
        //     route('employee.permission-requests.index'),
        //     100,
        //     PermissionEnum::PERMISSION_REQUESTS_VIEW_ANY->value,
        //     'employee.permission-requests.*',
        //     'FileText',
        // );

        return $next($request);
    }
}
