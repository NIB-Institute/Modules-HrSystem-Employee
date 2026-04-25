<?php

namespace Modules\Employee\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Employee\Actions\Dashboard\V1\Attendance\GetSelfServiceAttendanceAction;
use Modules\Employee\Actions\Dashboard\V1\Attendance\SelfServiceCheckInAction;
use Modules\Employee\Actions\Dashboard\V1\Attendance\SelfServiceCheckOutAction;
use Modules\Employee\Http\Requests\Dashboard\V1\SelfServiceCheckInRequest;
use Modules\Employee\Http\Requests\Dashboard\V1\SelfServiceCheckOutRequest;
use Modules\Employee\Models\Employee;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class SelfServiceAttendanceController extends Controller
{
    public function __construct(
        private GetSelfServiceAttendanceAction $selfServiceAction,
        private SelfServiceCheckInAction $checkInAction,
        private SelfServiceCheckOutAction $checkOutAction,
    ) {}

    /**
     * Check if user is a super-admin.
     */
    private function isSuperAdmin(): bool
    {
        $user = auth()->user();

        return $user->hasRole('super-admin');
    }

    /**
     * Get the authenticated employee or allow super-admin to select one.
     */
    private function getAuthenticatedEmployee(?int $employeeId = null): Employee
    {
        $user = auth()->user();

        // Super-admin can act as any employee
        if ($this->isSuperAdmin() && $employeeId) {
            $employee = Employee::find($employeeId);
            if ($employee) {
                return $employee;
            }
        }

        // Regular flow: find employee linked to user
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            // For super-admin without linked employee, throw special exception
            if ($this->isSuperAdmin()) {
                throw new AccessDeniedHttpException('Please select an employee to act as. Add ?employee_id=X to the URL.');
            }
            throw new AccessDeniedHttpException('You are not registered as an employee.');
        }

        return $employee;
    }

    /**
     * Display self-service attendance page for employees.
     * Super-admins can use ?employee_id=X to test as specific employee.
     */
    public function index(Request $request): Response
    {
        $employeeId = $request->query('employee_id');

        // For super-admin without employee_id, show employee selector
        if ($this->isSuperAdmin() && !$employeeId) {
            $user = auth()->user();
            $linkedEmployee = Employee::where('user_id', $user->id)->first();

            if (!$linkedEmployee) {
                // Show employee selector page for super-admin
                $employees = Employee::active()
                    ->select('id', 'uuid', 'first_name', 'last_name', 'employee_code', 'avatar_url', 'department_id')
                    ->with('department:id,name')
                    ->orderBy('first_name')
                    ->get()
                    ->map(fn ($e) => [
                        'id'    => $e->id,
                        'uuid'  => $e->uuid,
                        'full_name'     => $e->full_name,
                        'employee_code' => $e->employee_code,
                        'avatar_url'    => $e->avatar_url,
                        'department_name' => $e->department?->name,
                    ]);

                return Inertia::render('employee::Dashboard/V1/Attendance/SelfServiceSelect', [
                    'employees' => $employees,
                ]);
            }
        }

        $employee = $this->getAuthenticatedEmployee($employeeId ? (int) $employeeId : null);

        $data = $this->selfServiceAction->execute($employee->id);

        // Add super-admin context
        if ($this->isSuperAdmin()) {
            $data['isAdminMode'] = true;
            $data['selectedEmployeeId'] = $employee->id;
        }

        return Inertia::render('employee::Dashboard/V1/Attendance/SelfService', $data);
    }

    /**
     * Handle self-service check-in.
     */
    public function checkIn(SelfServiceCheckInRequest $request): RedirectResponse
    {
        $employeeId = $request->query('employee_id');
        $employee = $this->getAuthenticatedEmployee($employeeId ? (int) $employeeId : null);

        $result = $this->checkInAction->execute($employee->id, [
            'method'    => 'self_service',
            'latitude'  => $request->validated('latitude'),
            'longitude' => $request->validated('longitude'),
            'location'  => $request->validated('location'),
            'notes'     => $request->validated('notes'),
        ]);

        $redirectParams = $employeeId ? ['employee_id' => $employeeId] : [];

        if ($result['success']) {
            return redirect()
                ->route('employee.attendances.self-service', $redirectParams)
                ->with('success', $result['message']);
        }

        return redirect()
            ->route('employee.attendances.self-service', $redirectParams)
            ->with('error', $result['message']);
    }

    /**
     * Handle self-service check-out.
     */
    public function checkOut(SelfServiceCheckOutRequest $request): RedirectResponse
    {
        $employeeId = $request->query('employee_id');
        $employee = $this->getAuthenticatedEmployee($employeeId ? (int) $employeeId : null);

        $result = $this->checkOutAction->execute($employee->id, [
            'method'    => 'self_service',
            'latitude'  => $request->validated('latitude'),
            'longitude' => $request->validated('longitude'),
            'location'  => $request->validated('location'),
            'notes'     => $request->validated('notes'),
        ]);

        $redirectParams = $employeeId ? ['employee_id' => $employeeId] : [];

        if ($result['success']) {
            return redirect()
                ->route('employee.attendances.self-service', $redirectParams)
                ->with('success', $result['message']);
        }

        return redirect()
            ->route('employee.attendances.self-service', $redirectParams)
            ->with('error', $result['message']);
    }
}
