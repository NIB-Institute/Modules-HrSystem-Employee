<?php

namespace Modules\Employee\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Momentum\Modal\Modal;
use Modules\Employee\Actions\Dashboard\V1\EmployeeType\BulkDeleteEmployeeTypesAction;
use Modules\Employee\Actions\Dashboard\V1\EmployeeType\CreateEmployeeTypeAction;
use Modules\Employee\Actions\Dashboard\V1\EmployeeType\DeleteEmployeeTypeAction;
use Modules\Employee\Actions\Dashboard\V1\EmployeeType\GetEmployeeTypeEditDataAction;
use Modules\Employee\Actions\Dashboard\V1\EmployeeType\GetEmployeeTypeIndexDataAction;
use Modules\Employee\Actions\Dashboard\V1\EmployeeType\GetEmployeeTypeShowDataAction;
use Modules\Employee\Actions\Dashboard\V1\EmployeeType\ToggleEmployeeTypeStatusAction;
use Modules\Employee\Actions\Dashboard\V1\EmployeeType\UpdateEmployeeTypeAction;
use Modules\Employee\Http\Requests\Dashboard\V1\BulkDeleteEmployeeTypesRequest;
use Modules\Employee\Http\Requests\Dashboard\V1\StoreEmployeeTypeRequest;
use Modules\Employee\Http\Requests\Dashboard\V1\UpdateEmployeeTypeRequest;
use Modules\Employee\Http\Resources\Dashboard\V1\EmployeeTypeResource;
use Modules\Employee\Models\EmployeeType;

class EmployeeTypeController extends Controller
{
    public function __construct(
        protected GetEmployeeTypeIndexDataAction $getIndexDataAction,
        protected GetEmployeeTypeShowDataAction $getShowDataAction,
        protected GetEmployeeTypeEditDataAction $getEditDataAction,
        protected CreateEmployeeTypeAction $createAction,
        protected UpdateEmployeeTypeAction $updateAction,
        protected DeleteEmployeeTypeAction $deleteAction,
        protected ToggleEmployeeTypeStatusAction $toggleStatusAction,
        protected BulkDeleteEmployeeTypesAction $bulkDeleteAction,
    ) {}

    /**
     * Display a listing of employee types.
     */
    public function index(Request $request): Response
    {
        $perPage = $request->input('per_page', 10);
        $filters = $request->only(['search', 'status']);

        $data = $this->getIndexDataAction->execute($perPage, $filters);

        return Inertia::render('employee::Dashboard/V1/EmployeeType/Index', $data);
    }

    /**
     * Show the form for creating a new employee type.
     */
    public function create(): Modal
    {
        return Inertia::modal('employee::Dashboard/V1/EmployeeType/Create')
            ->baseRoute('employee.employee-types.index');
    }

    /**
     * Store a newly created employee type.
     */
    public function store(StoreEmployeeTypeRequest $request): RedirectResponse
    {
        $this->createAction->execute($request->validated());

        return redirect()
            ->route('employee.employee-types.index')
            ->with('success', 'Employee type created successfully.');
    }

    /**
     * Display the specified employee type.
     */
    public function show(EmployeeType $employeeType): Response
    {
        $data = $this->getShowDataAction->execute($employeeType);

        return Inertia::render('employee::Dashboard/V1/EmployeeType/Show', $data);
    }

    /**
     * Show the form for editing the employee type.
     */
    public function edit(EmployeeType $employeeType): Modal
    {
        $data = $this->getEditDataAction->execute($employeeType);

        return Inertia::modal('employee::Dashboard/V1/EmployeeType/Edit', $data)
            ->baseRoute('employee.employee-types.index');
    }

    /**
     * Update the specified employee type.
     */
    public function update(UpdateEmployeeTypeRequest $request, EmployeeType $employeeType): RedirectResponse
    {
        $this->updateAction->execute($employeeType, $request->validated());

        return redirect()
            ->route('employee.employee-types.index')
            ->with('success', 'Employee type updated successfully.');
    }

    /**
     * Show delete confirmation modal.
     */
    public function confirmDelete(EmployeeType $employeeType): Modal
    {
        $employeeType->loadCount('employees');

        return Inertia::modal('employee::Dashboard/V1/EmployeeType/Delete', [
            'employeeType' => (new EmployeeTypeResource($employeeType))->resolve(),
        ])->baseRoute('employee.employee-types.index');
    }

    /**
     * Remove the specified employee type.
     */
    public function destroy(EmployeeType $employeeType): RedirectResponse
    {
        $this->deleteAction->execute($employeeType);

        return redirect()
            ->route('employee.employee-types.index')
            ->with('success', 'Employee type deleted successfully.');
    }

    /**
     * Toggle employee type status.
     */
    public function toggleStatus(EmployeeType $employeeType): RedirectResponse
    {
        $this->toggleStatusAction->execute($employeeType);

        $status = $employeeType->status ? 'activated' : 'deactivated';

        return redirect()
            ->back()
            ->with('success', "Employee type {$status} successfully.");
    }

    /**
     * Show bulk delete confirmation modal.
     */
    public function confirmBulkDelete(Request $request): Modal
    {
        $uuids = $request->input('uuids', []);

        $employeeTypes = EmployeeType::whereIn('uuid', $uuids)->withCount('employees')->get();

        return Inertia::modal('employee::Dashboard/V1/EmployeeType/BulkDelete', [
            'employeeTypes' => EmployeeTypeResource::collection($employeeTypes)->resolve(),
        ])->baseRoute('employee.employee-types.index');
    }

    /**
     * Bulk delete employee types.
     */
    public function bulkDelete(BulkDeleteEmployeeTypesRequest $request): RedirectResponse
    {
        $result = $this->bulkDeleteAction->execute($request->validated('uuids'));

        $message = "{$result['deleted']} employee type(s) deleted successfully.";

        if ($result['failed'] > 0) {
            $message .= " {$result['failed']} employee type(s) could not be found.";
        }

        return redirect()
            ->route('employee.employee-types.index')
            ->with('success', $message);
    }
}
