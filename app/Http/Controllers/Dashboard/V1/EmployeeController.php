<?php

namespace Modules\Employee\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Momentum\Modal\Modal;
use Modules\Employee\Actions\Dashboard\V1\Attendance\GenerateQrCodeAction;
use Modules\Employee\Actions\Dashboard\V1\BulkDeleteEmployeesAction;
use Modules\Employee\Actions\Dashboard\V1\CreateEmployeeAction;
use Modules\Employee\Actions\Dashboard\V1\DeleteEmployeeAction;
use Modules\Employee\Actions\Dashboard\V1\GetEmployeeCreateDataAction;
use Modules\Employee\Actions\Dashboard\V1\GetEmployeeEditDataAction;
use Modules\Employee\Actions\Dashboard\V1\GetEmployeeIndexDataAction;
use Modules\Employee\Actions\Dashboard\V1\GetEmployeeShowDataAction;
use Modules\Employee\Actions\Dashboard\V1\ToggleEmployeeStatusAction;
use Modules\Employee\Actions\Dashboard\V1\UpdateEmployeeAction;
use Modules\Employee\Http\Requests\Dashboard\V1\BulkDeleteEmployeesRequest;
use Modules\Employee\Http\Requests\Dashboard\V1\StoreEmployeeRequest;
use Modules\Employee\Http\Requests\Dashboard\V1\UpdateEmployeeRequest;
use Modules\Employee\Http\Resources\Dashboard\V1\EmployeeResource;
use Modules\Employee\Models\Employee;
use Modules\Employee\Services\EmployeeService;
use Modules\School\Models\Department;
use Modules\School\Models\School;

class EmployeeController extends Controller
{
    public function __construct(
        protected EmployeeService $employeeService,
        protected GetEmployeeIndexDataAction $getIndexDataAction,
        protected GetEmployeeShowDataAction $getShowDataAction,
        protected GetEmployeeCreateDataAction $getCreateDataAction,
        protected GetEmployeeEditDataAction $getEditDataAction,
        protected CreateEmployeeAction $createAction,
        protected UpdateEmployeeAction $updateAction,
        protected DeleteEmployeeAction $deleteAction,
        protected ToggleEmployeeStatusAction $toggleStatusAction,
        protected BulkDeleteEmployeesAction $bulkDeleteAction,
    ) {}

    /**
     * Display a listing of employees.
     */
    public function index(Request $request): Response
    {
        $perPage = $request->input('per_page', 10);
        $filters = $request->only(['search', 'status', 'employee_type', 'school_id', 'department_id', 'date_from', 'date_to']);

        $data = $this->getIndexDataAction->execute($perPage, $filters);

        return Inertia::render('employee::Dashboard/V1/Employee/Index', $data);
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create(Request $request): Response
    {
        $schoolId = $request->input('school_id') ? (int) $request->input('school_id') : null;
        $data = $this->getCreateDataAction->execute($schoolId);

        // Add generated employee code
        $data['generatedCode'] = $this->employeeService->generateEmployeeCode();

        return Inertia::render('employee::Dashboard/V1/Employee/Create', $data);
    }

    /**
     * Store a newly created employee.
     */
    public function store(StoreEmployeeRequest $request): RedirectResponse
    {
        $this->createAction->execute($request->validated());

        return redirect()
            ->route('employee.employees.index')
            ->with('success', 'Employee created successfully.');
    }

    /**
     * Display the specified employee.
     */
    public function show(Employee $employee): Response
    {
        $data = $this->getShowDataAction->execute($employee);

        return Inertia::render('employee::Dashboard/V1/Employee/Show', $data);
    }

    /**
     * Show the form for editing the employee.
     */
    public function edit(Employee $employee): Response
    {
        $data = $this->getEditDataAction->execute($employee);

        return Inertia::render('employee::Dashboard/V1/Employee/Edit', $data);
    }

    /**
     * Update the specified employee.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        $this->updateAction->execute($employee, $request->validated());

        return redirect()
            ->route('employee.employees.index')
            ->with('success', 'Employee updated successfully.');
    }

    /**
     * Show delete confirmation modal.
     */
    public function confirmDelete(Employee $employee): Modal
    {
        $employee->load(['school', 'department']);
        $employee->loadCount('courses');

        return Inertia::modal('employee::Dashboard/V1/Employee/Delete', [
            'employee' => (new EmployeeResource($employee))->resolve(),
        ])->baseRoute('employee.employees.index');
    }

    /**
     * Remove the specified employee.
     */
    public function destroy(Employee $employee): RedirectResponse
    {
        $this->deleteAction->execute($employee);

        return redirect()
            ->route('employee.employees.index')
            ->with('success', 'Employee deleted successfully.');
    }

    /**
     * Toggle employee status.
     */
    public function toggleStatus(Employee $employee): RedirectResponse
    {
        $this->toggleStatusAction->execute($employee);

        $status = $employee->status ? 'activated' : 'deactivated';

        return redirect()
            ->back()
            ->with('success', "Employee {$status} successfully.");
    }

    /**
     * Get departments for a specific school (AJAX).
     */
    public function getDepartments(Request $request): \Illuminate\Http\JsonResponse
    {
        $schoolId = $request->input('school_id');

        try {
            $departments = Department::where('school_id', $schoolId)
                ->where('status', true)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
        } catch (\Exception $e) {
            $departments = collect([]);
        }

        return response()->json($departments);
    }

    /**
     * Show QR code page for employee badge.
     */
    public function qrCode(Employee $employee, GenerateQrCodeAction $generateQrAction): Response
    {
        $employee->load(['school', 'department', 'employeeType']);

        // Generate or get existing QR code
        $qrCodeData = $generateQrAction->generateEmployeeQr($employee);

        return Inertia::render('employee::Dashboard/V1/Employee/QrCode', [
            'employee' => [
                'id' => $employee->id,
                'uuid' => $employee->uuid,
                'employee_code' => $employee->employee_code,
                'employee_qr_code' => $employee->employee_qr_code,
                'full_name' => $employee->full_name,
                'first_name' => $employee->first_name,
                'last_name' => $employee->last_name,
                'job_title' => $employee->job_title,
                'avatar_url' => $employee->avatar_url,
                'department_name' => $employee->department?->name,
                'school_name' => $employee->school?->name,
                'employee_type_name' => $employee->employeeType?->name,
            ],
            'qrData' => $qrCodeData['qr_data'],
        ]);
    }

    /**
     * Regenerate QR code for employee.
     */
    public function regenerateQrCode(Employee $employee, GenerateQrCodeAction $generateQrAction): RedirectResponse
    {
        $generateQrAction->regenerateEmployeeQr($employee);

        return redirect()
            ->back()
            ->with('success', 'QR code regenerated successfully.');
    }

    /**
     * Show bulk delete confirmation modal.
     */
    public function confirmBulkDelete(Request $request): Modal
    {
        $uuids = $request->input('uuids', []);

        $employees = Employee::whereIn('uuid', $uuids)->get();

        return Inertia::modal('employee::Dashboard/V1/Employee/BulkDelete', [
            'employees' => EmployeeResource::collection($employees)->resolve(),
        ])->baseRoute('employee.employees.index');
    }

    /**
     * Bulk delete employees.
     */
    public function bulkDelete(BulkDeleteEmployeesRequest $request): RedirectResponse
    {
        $result = $this->bulkDeleteAction->execute($request->validated('uuids'));

        $message = "{$result['deleted']} employee(s) deleted successfully.";

        if ($result['failed'] > 0) {
            $message .= " {$result['failed']} employee(s) could not be found.";
        }

        return redirect()
            ->route('employee.employees.index')
            ->with('success', $message);
    }
}
