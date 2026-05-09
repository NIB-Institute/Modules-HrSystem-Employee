<?php

namespace Modules\Employee\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Momentum\Modal\Modal;
use Modules\Employee\Actions\Dashboard\V1\EmployeePlanAssignment\BulkAssignEmployeePlanAction;
use Modules\Employee\Actions\Dashboard\V1\EmployeePlanAssignment\CreateEmployeePlanAssignmentAction;
use Modules\Employee\Actions\Dashboard\V1\EmployeePlanAssignment\DeleteEmployeePlanAssignmentAction;
use Modules\Employee\Actions\Dashboard\V1\EmployeePlanAssignment\GetEmployeePlanAssignmentCreateDataAction;
use Modules\Employee\Actions\Dashboard\V1\EmployeePlanAssignment\GetEmployeePlanAssignmentEditDataAction;
use Modules\Employee\Actions\Dashboard\V1\EmployeePlanAssignment\GetEmployeePlanAssignmentIndexDataAction;
use Modules\Employee\Actions\Dashboard\V1\EmployeePlanAssignment\GetEmployeePlanAssignmentShowDataAction;
use Modules\Employee\Actions\Dashboard\V1\EmployeePlanAssignment\UpdateEmployeePlanAssignmentAction;
use Modules\Employee\Http\Requests\Dashboard\V1\BulkAssignEmployeePlanRequest;
use Modules\Employee\Http\Requests\Dashboard\V1\StoreEmployeePlanAssignmentRequest;
use Modules\Employee\Http\Requests\Dashboard\V1\UpdateEmployeePlanAssignmentRequest;
use Modules\Employee\Http\Resources\Dashboard\V1\EmployeePlanAssignmentResource;
use Modules\Employee\Models\EmployeePlanAssignment;

class EmployeePlanAssignmentController extends Controller
{
    public function __construct(
        protected GetEmployeePlanAssignmentIndexDataAction $getIndexDataAction,
        protected GetEmployeePlanAssignmentCreateDataAction $getCreateDataAction,
        protected GetEmployeePlanAssignmentEditDataAction $getEditDataAction,
        protected GetEmployeePlanAssignmentShowDataAction $getShowDataAction,
        protected CreateEmployeePlanAssignmentAction $createAction,
        protected UpdateEmployeePlanAssignmentAction $updateAction,
        protected DeleteEmployeePlanAssignmentAction $deleteAction,
        protected BulkAssignEmployeePlanAction $bulkAssignAction,
    ) {}

    public function index(Request $request): Response
    {
        $perPage = (int) $request->input('per_page', 15);
        $filters = $request->only(['search', 'employee_plan_id', 'employee_id', 'status']);

        $data = $this->getIndexDataAction->execute($perPage, $filters);

        return Inertia::render('employee::Dashboard/V1/EmployeePlanAssignment/Index', $data);
    }

    public function create(Request $request): Modal
    {
        $planId = $request->input('employee_plan_id');
        $employeeId = $request->input('employee_id');

        $data = $this->getCreateDataAction->execute(
            $planId ? (int) $planId : null,
            $employeeId ? (int) $employeeId : null,
        );

        return Inertia::modal('employee::Dashboard/V1/EmployeePlanAssignment/Create', $data)
            ->baseRoute('employee.employee-plan-assignments.index');
    }

    public function store(StoreEmployeePlanAssignmentRequest $request): RedirectResponse
    {
        $this->createAction->execute($request->validated());

        return redirect()
            ->route('employee.employee-plan-assignments.index')
            ->with('success', 'Employee assigned to plan successfully.');
    }

    public function bulkAssign(BulkAssignEmployeePlanRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $result = $this->bulkAssignAction->execute(
            (int) $validated['employee_plan_id'],
            $validated['employee_ids'],
            $validated['notes'] ?? null,
        );

        $msg = "Bulk assignment complete: {$result['created']} created, {$result['skipped']} skipped.";

        return redirect()
            ->route('employee.employee-plan-assignments.index')
            ->with('success', $msg);
    }

    public function show(EmployeePlanAssignment $employeePlanAssignment): Response
    {
        $data = $this->getShowDataAction->execute($employeePlanAssignment);

        return Inertia::render('employee::Dashboard/V1/EmployeePlanAssignment/Show', $data);
    }

    public function edit(EmployeePlanAssignment $employeePlanAssignment): Modal
    {
        $data = $this->getEditDataAction->execute($employeePlanAssignment);

        return Inertia::modal('employee::Dashboard/V1/EmployeePlanAssignment/Edit', $data)
            ->baseRoute('employee.employee-plan-assignments.index');
    }

    public function update(UpdateEmployeePlanAssignmentRequest $request, EmployeePlanAssignment $employeePlanAssignment): RedirectResponse
    {
        $this->updateAction->execute($employeePlanAssignment, $request->validated());

        return redirect()
            ->route('employee.employee-plan-assignments.index')
            ->with('success', 'Assignment updated successfully.');
    }

    public function confirmDelete(EmployeePlanAssignment $employeePlanAssignment): Modal
    {
        $employeePlanAssignment->load(['plan', 'employee']);

        return Inertia::modal('employee::Dashboard/V1/EmployeePlanAssignment/Delete', [
            'assignment' => (new EmployeePlanAssignmentResource($employeePlanAssignment))->resolve(),
        ])->baseRoute('employee.employee-plan-assignments.index');
    }

    public function destroy(EmployeePlanAssignment $employeePlanAssignment): RedirectResponse
    {
        $this->deleteAction->execute($employeePlanAssignment);

        return redirect()
            ->route('employee.employee-plan-assignments.index')
            ->with('success', 'Assignment removed successfully.');
    }
}
