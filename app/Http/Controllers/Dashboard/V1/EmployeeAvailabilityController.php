<?php

namespace Modules\Employee\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Momentum\Modal\Modal;
use Modules\Employee\Actions\Dashboard\V1\EmployeeAvailability\CreateEmployeeAvailabilityAction;
use Modules\Employee\Actions\Dashboard\V1\EmployeeAvailability\DeleteEmployeeAvailabilityAction;
use Modules\Employee\Actions\Dashboard\V1\EmployeeAvailability\GetEmployeeAvailabilityCreateDataAction;
use Modules\Employee\Actions\Dashboard\V1\EmployeeAvailability\GetEmployeeAvailabilityEditDataAction;
use Modules\Employee\Actions\Dashboard\V1\EmployeeAvailability\GetEmployeeAvailabilityIndexDataAction;
use Modules\Employee\Actions\Dashboard\V1\EmployeeAvailability\UpdateEmployeeAvailabilityAction;
use Modules\Employee\Http\Requests\Dashboard\V1\StoreEmployeeAvailabilityRequest;
use Modules\Employee\Http\Requests\Dashboard\V1\UpdateEmployeeAvailabilityRequest;
use Modules\Employee\Http\Resources\Dashboard\V1\EmployeeAvailabilityResource;
use Modules\Employee\Models\EmployeeAvailability;

class EmployeeAvailabilityController extends Controller
{
    public function __construct(
        protected GetEmployeeAvailabilityIndexDataAction $getIndexDataAction,
        protected GetEmployeeAvailabilityCreateDataAction $getCreateDataAction,
        protected GetEmployeeAvailabilityEditDataAction $getEditDataAction,
        protected CreateEmployeeAvailabilityAction $createAction,
        protected UpdateEmployeeAvailabilityAction $updateAction,
        protected DeleteEmployeeAvailabilityAction $deleteAction,
    ) {}

    public function index(Request $request): Response
    {
        $perPage = (int) $request->input('per_page', 15);
        $filters = $request->only(['search', 'employee_id', 'day_of_week', 'is_active']);

        $data = $this->getIndexDataAction->execute($perPage, $filters);

        return Inertia::render('employee::Dashboard/V1/EmployeeAvailability/Index', $data);
    }

    public function create(Request $request): Modal
    {
        $employeeId = $request->input('employee_id');
        $data = $this->getCreateDataAction->execute($employeeId ? (int) $employeeId : null);

        return Inertia::modal('employee::Dashboard/V1/EmployeeAvailability/Create', $data)
            ->baseRoute('employee.employee-availabilities.index');
    }

    public function store(StoreEmployeeAvailabilityRequest $request): RedirectResponse
    {
        $this->createAction->execute($request->validated());

        return redirect()
            ->route('employee.employee-availabilities.index')
            ->with('success', 'Availability slot created successfully.');
    }

    public function edit(EmployeeAvailability $employeeAvailability): Modal
    {
        $data = $this->getEditDataAction->execute($employeeAvailability);

        return Inertia::modal('employee::Dashboard/V1/EmployeeAvailability/Edit', $data)
            ->baseRoute('employee.employee-availabilities.index');
    }

    public function update(UpdateEmployeeAvailabilityRequest $request, EmployeeAvailability $employeeAvailability): RedirectResponse
    {
        $this->updateAction->execute($employeeAvailability, $request->validated());

        return redirect()
            ->route('employee.employee-availabilities.index')
            ->with('success', 'Availability slot updated successfully.');
    }

    public function confirmDelete(EmployeeAvailability $employeeAvailability): Modal
    {
        $employeeAvailability->load('employee');

        return Inertia::modal('employee::Dashboard/V1/EmployeeAvailability/Delete', [
            'availability' => (new EmployeeAvailabilityResource($employeeAvailability))->resolve(),
        ])->baseRoute('employee.employee-availabilities.index');
    }

    public function destroy(EmployeeAvailability $employeeAvailability): RedirectResponse
    {
        $this->deleteAction->execute($employeeAvailability);

        return redirect()
            ->route('employee.employee-availabilities.index')
            ->with('success', 'Availability slot deleted successfully.');
    }
}
