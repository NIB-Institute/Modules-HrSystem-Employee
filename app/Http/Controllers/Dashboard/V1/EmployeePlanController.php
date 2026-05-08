<?php

namespace Modules\Employee\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Momentum\Modal\Modal;
use Modules\Employee\Actions\Dashboard\V1\EmployeePlan\CreateEmployeePlanAction;
use Modules\Employee\Actions\Dashboard\V1\EmployeePlan\DeleteEmployeePlanAction;
use Modules\Employee\Actions\Dashboard\V1\EmployeePlan\GetEmployeePlanCreateDataAction;
use Modules\Employee\Actions\Dashboard\V1\EmployeePlan\GetEmployeePlanEditDataAction;
use Modules\Employee\Actions\Dashboard\V1\EmployeePlan\GetEmployeePlanIndexDataAction;
use Modules\Employee\Actions\Dashboard\V1\EmployeePlan\UpdateEmployeePlanAction;
use Modules\Employee\Http\Requests\Dashboard\V1\StoreEmployeePlanRequest;
use Modules\Employee\Http\Requests\Dashboard\V1\UpdateEmployeePlanRequest;
use Modules\Employee\Http\Resources\Dashboard\V1\EmployeePlanResource;
use Modules\Employee\Models\EmployeePlan;

class EmployeePlanController extends Controller
{
    public function __construct(
        protected GetEmployeePlanIndexDataAction $getIndexDataAction,
        protected GetEmployeePlanCreateDataAction $getCreateDataAction,
        protected GetEmployeePlanEditDataAction $getEditDataAction,
        protected CreateEmployeePlanAction $createAction,
        protected UpdateEmployeePlanAction $updateAction,
        protected DeleteEmployeePlanAction $deleteAction,
    ) {}

    public function index(Request $request): Response
    {
        $perPage = (int) $request->input('per_page', 10);
        $filters = $request->only(['search', 'employee_id', 'status', 'priority', 'date_from', 'date_to']);

        $data = $this->getIndexDataAction->execute($perPage, $filters);

        return Inertia::render('employee::Dashboard/V1/EmployeePlan/Index', $data);
    }

    public function create(Request $request): Modal
    {
        $employeeId = $request->input('employee_id');
        $data = $this->getCreateDataAction->execute($employeeId ? (int) $employeeId : null);

        return Inertia::modal('employee::Dashboard/V1/EmployeePlan/Create', $data)
            ->baseRoute('employee.employee-plans.index');
    }

    public function store(StoreEmployeePlanRequest $request): RedirectResponse
    {
        $this->createAction->execute($request->validated());

        return redirect()
            ->route('employee.employee-plans.index')
            ->with('success', 'Employee plan created successfully.');
    }

    public function edit(EmployeePlan $employeePlan): Modal
    {
        $data = $this->getEditDataAction->execute($employeePlan);

        return Inertia::modal('employee::Dashboard/V1/EmployeePlan/Edit', $data)
            ->baseRoute('employee.employee-plans.index');
    }

    public function update(UpdateEmployeePlanRequest $request, EmployeePlan $employeePlan): RedirectResponse
    {
        $this->updateAction->execute($employeePlan, $request->validated());

        return redirect()
            ->route('employee.employee-plans.index')
            ->with('success', 'Employee plan updated successfully.');
    }

    public function confirmDelete(EmployeePlan $employeePlan): Modal
    {
        $employeePlan->load('employee');

        return Inertia::modal('employee::Dashboard/V1/EmployeePlan/Delete', [
            'plan' => (new EmployeePlanResource($employeePlan))->resolve(),
        ])->baseRoute('employee.employee-plans.index');
    }

    public function destroy(EmployeePlan $employeePlan): RedirectResponse
    {
        $this->deleteAction->execute($employeePlan);

        return redirect()
            ->route('employee.employee-plans.index')
            ->with('success', 'Employee plan deleted successfully.');
    }
}
