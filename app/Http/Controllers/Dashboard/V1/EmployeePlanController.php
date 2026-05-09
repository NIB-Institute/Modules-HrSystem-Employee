<?php

namespace Modules\Employee\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Momentum\Modal\Modal;
use Modules\Employee\Actions\Dashboard\V1\EmployeePlan\CreateEmployeePlanAction;
use Modules\Employee\Actions\Dashboard\V1\EmployeePlan\DeleteEmployeePlanAction;
use Modules\Employee\Actions\Dashboard\V1\EmployeePlan\GetEmployeePlanCreateDataAction;
use Modules\Employee\Actions\Dashboard\V1\EmployeePlan\GetEmployeePlanEditDataAction;
use Modules\Employee\Actions\Dashboard\V1\EmployeePlan\GetEmployeePlanIndexDataAction;
use Modules\Employee\Actions\Dashboard\V1\EmployeePlan\GetEmployeePlanShowDataAction;
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
        protected GetEmployeePlanShowDataAction $getShowDataAction,
        protected CreateEmployeePlanAction $createAction,
        protected UpdateEmployeePlanAction $updateAction,
        protected DeleteEmployeePlanAction $deleteAction,
    ) {}

    public function index(Request $request): Response
    {
        $perPage = (int) $request->input('per_page', 10);
        $filters = $request->only(['search', 'status', 'priority', 'date_from', 'date_to']);

        $data = $this->getIndexDataAction->execute($perPage, $filters);

        return Inertia::render('employee::Dashboard/V1/EmployeePlan/Index', $data);
    }

    public function create(): Modal
    {
        $data = $this->getCreateDataAction->execute();

        return Inertia::modal('employee::Dashboard/V1/EmployeePlan/Create', $data)
            ->baseRoute('employee.employee-plans.index');
    }

    public function store(StoreEmployeePlanRequest $request): RedirectResponse
    {
        $payload = $request->validated();
        $payload['created_by'] = Auth::id();

        $this->createAction->execute($payload);

        return redirect()
            ->route('employee.employee-plans.index')
            ->with('success', 'Plan created successfully.');
    }

    public function show(Request $request, EmployeePlan $employeePlan): Response
    {
        $perPage = (int) $request->input('per_page', 15);
        $data = $this->getShowDataAction->execute($employeePlan, $perPage);

        return Inertia::render('employee::Dashboard/V1/EmployeePlan/Show', $data);
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
            ->with('success', 'Plan updated successfully.');
    }

    public function confirmDelete(EmployeePlan $employeePlan): Modal
    {
        $employeePlan->loadCount('assignments');

        return Inertia::modal('employee::Dashboard/V1/EmployeePlan/Delete', [
            'plan' => (new EmployeePlanResource($employeePlan))->resolve(),
        ])->baseRoute('employee.employee-plans.index');
    }

    public function destroy(EmployeePlan $employeePlan): RedirectResponse
    {
        $this->deleteAction->execute($employeePlan);

        return redirect()
            ->route('employee.employee-plans.index')
            ->with('success', 'Plan deleted successfully.');
    }
}
