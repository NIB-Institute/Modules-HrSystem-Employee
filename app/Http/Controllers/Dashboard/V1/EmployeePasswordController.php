<?php

namespace Modules\Employee\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Momentum\Modal\Modal;
use Modules\Employee\Actions\Dashboard\V1\ChangeEmployeePasswordAction;
use Modules\Employee\Actions\Dashboard\V1\CreateEmployeeAccountAction;
use Modules\Employee\Http\Requests\Dashboard\V1\ChangeEmployeePasswordRequest;
use Modules\Employee\Http\Requests\Dashboard\V1\CreateEmployeeAccountRequest;
use Modules\Employee\Models\Employee;

class EmployeePasswordController extends Controller
{
    public function __construct(
        protected ChangeEmployeePasswordAction $changePasswordAction,
        protected CreateEmployeeAccountAction $createAccountAction,
    ) {}

    /**
     * Show change password modal for employee.
     */
    public function edit(Employee $employee): Modal|RedirectResponse
    {
        $employee->load('user');

        if (!$employee->user_id) {
            return redirect()
                ->route('employee.employees.show', $employee)
                ->with('error', 'This employee does not have a linked user account.');
        }

        // Get all employees with accounts for the searchable select
        $employeesWithAccounts = Employee::whereNotNull('user_id')
            ->with('user')
            ->orderBy('first_name')
            ->get()
            ->map(fn ($emp) => [
                'value' => $emp->uuid,
                'label' => $emp->full_name,
                'description' => $emp->employee_code,
                'avatar_url' => $emp->avatar_url,
            ]);

        return Inertia::modal('employee::Dashboard/V1/Employee/ChangePassword', [
            'employee' => [
                'id' => $employee->id,
                'uuid' => $employee->uuid,
                'full_name' => $employee->full_name,
                'employee_code' => $employee->employee_code,
                'email' => $employee->email,
                'avatar_url' => $employee->avatar_url,
                'user' => $employee->user ? [
                    'id' => $employee->user->id,
                    'name' => $employee->user->name,
                    'email' => $employee->user->email,
                ] : null,
            ],
            'employeeOptions' => $employeesWithAccounts,
        ])->baseRoute('employee.employees.index');
    }

    /**
     * Update the employee's user password.
     */
    public function update(ChangeEmployeePasswordRequest $request, Employee $employee): RedirectResponse
    {
        $result = $this->changePasswordAction->execute($employee, $request->validated('password'));

        if (!$result['success']) {
            return redirect()
                ->back()
                ->with('error', $result['message']);
        }

        return redirect()
            ->route('employee.employees.show', $employee)
            ->with('success', $result['message']);
    }

    /**
     * Show create account modal for employee.
     */
    public function showCreateAccount(Employee $employee): Modal|RedirectResponse
    {
        if ($employee->user_id) {
            return redirect()
                ->route('employee.employees.show', $employee)
                ->with('error', 'This employee already has a linked user account.');
        }

        // Must have email or phone to create account
        if (!$employee->email && !$employee->phone_number) {
            return redirect()
                ->route('employee.employees.show', $employee)
                ->with('error', 'This employee does not have an email or phone number.');
        }

        // Get all employees without accounts (have email or phone) for the searchable select
        $employeesWithoutAccounts = Employee::whereNull('user_id')
            ->where(function ($query) {
                $query->whereNotNull('email')
                    ->orWhereNotNull('phone_number');
            })
            ->orderBy('first_name')
            ->get()
            ->map(fn ($emp) => [
                'value' => $emp->uuid,
                'label' => $emp->full_name,
                'description' => $emp->employee_code,
                'avatar_url' => $emp->avatar_url,
            ]);

        return Inertia::modal('employee::Dashboard/V1/Employee/CreateAccount', [
            'employee' => [
                'id' => $employee->id,
                'uuid' => $employee->uuid,
                'full_name' => $employee->full_name,
                'employee_code' => $employee->employee_code,
                'email' => $employee->email,
                'phone_number' => $employee->phone_number,
                'avatar_url' => $employee->avatar_url,
            ],
            'employeeOptions' => $employeesWithoutAccounts,
        ])->baseRoute('employee.employees.index');
    }

    /**
     * Create a user account for an existing employee.
     */
    public function createAccount(CreateEmployeeAccountRequest $request, Employee $employee): RedirectResponse
    {
        $result = $this->createAccountAction->execute(
            $employee,
            $request->validated('password'),
            $request->validated('login_method')
        );

        if (!$result['success']) {
            return redirect()
                ->back()
                ->with('error', $result['message']);
        }

        return redirect()
            ->route('employee.employees.show', $employee)
            ->with('success', $result['message']);
    }
}
