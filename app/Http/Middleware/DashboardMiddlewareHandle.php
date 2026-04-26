<?php

namespace Modules\Employee\Http\Middleware;

use App\Services\MenuService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * Registers the Employee module's primary sidebar menu and its submenus
 * on every dashboard request, before HandleInertiaRequests serializes
 * the menu tree for the frontend.
 *
 * Menu items are declared as a structured array (see menu()), then
 * pushed into MenuService. Submenus that depend on optional routes are
 * guarded by Route::has() so unwired pages don't show in the sidebar.
 * Per-user permission filtering happens later in
 * MenuService::getMenuForUser().
 */
class DashboardMiddlewareHandle
{
    public function handle(Request $request, Closure $next)
    {
        $this->registerSidebarMenu();

        return $next($request);
    }

    protected function registerSidebarMenu(): void
    {
        $menu = $this->menu();

        MenuService::addMenuItem(
            menu: 'primary',
            id: $menu['id'],
            title: $menu['title'],
            url: $menu['url'],
            icon: $menu['icon'],
            order: $menu['order'],
            permissions: $menu['permissions'] ?? null,
            route: $menu['route'] ?? null,
        );

        foreach ($menu['submenus'] as $submenu) {
            if (isset($submenu['route_name']) && ! Route::has($submenu['route_name'])) {
                continue;
            }

            MenuService::addSubmenuItem(
                'primary',
                $menu['id'],
                $submenu['title'],
                $submenu['url'],
                $submenu['order'],
                $submenu['permissions'] ?? null,
                $submenu['route'] ?? null,
                $submenu['icon'] ?? null,
            );
        }
    }

    /**
     * Sidebar menu definition for the Employee module.
     *
     * @return array<string, mixed>
     */
    protected function menu(): array
    {
        return [
            'id' => 'employee',
            'title' => __('Employees'),
            'url' => route('employee.employees.index'),
            'icon' => 'Users',
            'order' => 50,
            'permissions' => 'employees.view_any',
            'route' => 'employee.*',
            'submenus' => [
                [
                    'title' => __('All Employees'),
                    'url' => route('employee.employees.index'),
                    'order' => 10,
                    'permissions' => 'employees.view_any',
                    'route' => 'employee.employees.*',
                    'icon' => 'Users',
                ],
                [
                    'title' => __('Employee Types'),
                    'url' => route('employee.employee-types.index'),
                    'order' => 20,
                    'permissions' => 'employee_types.view_any',
                    'route' => 'employee.employee-types.*',
                    'icon' => 'Tags',
                ],
                [
                    'title' => __('Attendance'),
                    'url' => route('employee.attendances.index'),
                    'order' => 30,
                    'permissions' => 'attendances.view_any',
                    'route' => 'employee.attendances.*',
                    'icon' => 'ClipboardCheck',
                    'route_name' => 'employee.attendances.index',
                ],
                [
                    'title' => __('QR Scanner'),
                    'url' => route('employee.attendances.scanner'),
                    'order' => 40,
                    'permissions' => 'attendances.scan_qr',
                    'route' => 'employee.attendances.scanner',
                    'icon' => 'QrCode',
                    'route_name' => 'employee.attendances.scanner',
                ],
                [
                    'title' => __('Scan Locations'),
                    'url' => route('employee.locations.index'),
                    'order' => 50,
                    'permissions' => 'locations.view_any',
                    'route' => 'employee.locations.*',
                    'icon' => 'MapPin',
                    'route_name' => 'employee.locations.index',
                ],
                [
                    'title' => __('Permission Requests'),
                    'url' => route('employee.permission-requests.index'),
                    'order' => 60,
                    'permissions' => 'permission_requests.view_any',
                    'route' => 'employee.permission-requests.*',
                    'icon' => 'FileText',
                    'route_name' => 'employee.permission-requests.index',
                ],
                [
                    'title' => __('Self Service'),
                    'url' => route('employee.attendances.self-service'),
                    'order' => 70,
                    'permissions' => null, // any authenticated employee
                    'route' => 'employee.attendances.self-service',
                    'icon' => 'LogIn',
                    'route_name' => 'employee.attendances.self-service',
                ],
            ],
        ];
    }
}
