<?php

namespace Modules\Employee\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Momentum\Modal\Modal;
use Modules\Employee\Actions\Dashboard\V1\Attendance\BulkDeleteAttendancesAction;
use Modules\Employee\Actions\Dashboard\V1\Attendance\GetAttendanceIndexDataAction;
use Modules\Employee\Actions\Dashboard\V1\Attendance\GetAttendanceShowDataAction;
use Modules\Employee\Actions\Dashboard\V1\Attendance\GetAttendanceAnalyticsAction;
use Modules\Employee\Actions\Dashboard\V1\Attendance\CreateManualAttendanceAction;
use Modules\Employee\Actions\Dashboard\V1\Attendance\UpdateAttendanceAction;
use Modules\Employee\Actions\Dashboard\V1\Attendance\ProcessQrScanAction;
use Modules\Employee\Actions\Dashboard\V1\Attendance\GenerateQrCodeAction;
use Modules\Employee\Http\Requests\Dashboard\V1\BulkDeleteAttendancesRequest;
use Modules\Employee\Http\Requests\Dashboard\V1\StoreAttendanceRequest;
use Modules\Employee\Http\Requests\Dashboard\V1\UpdateAttendanceRequest;
use Modules\Employee\Http\Resources\Dashboard\V1\AttendanceResource;
use Modules\Employee\Models\Attendance;
use Modules\Employee\Models\Employee;
use Modules\School\Models\Department;
use Modules\School\Models\Classroom;

class AttendanceController extends Controller
{
    public function __construct(
        private GetAttendanceIndexDataAction $indexAction,
        private GetAttendanceShowDataAction $showAction,
        private GetAttendanceAnalyticsAction $analyticsAction,
        private CreateManualAttendanceAction $createAction,
        private UpdateAttendanceAction $updateAction,
        private ProcessQrScanAction $qrScanAction,
        private GenerateQrCodeAction $generateQrAction,
        private BulkDeleteAttendancesAction $bulkDeleteAction,
    ) {}

    /**
     * Display attendance list.
     */
    public function index(Request $request): Response
    {
        $data = $this->indexAction->execute($request);

        return Inertia::render('employee::Dashboard/V1/Attendance/Index', [
            'attendances' => AttendanceResource::collection($data['attendances'])->response()->getData(true),
            'filters' => $data['filters'],
            'stats' => $data['stats'],
            'statuses' => $data['statuses'],
            'employeeOptions' => $data['employees'],
            'departmentOptions' => $data['departments'],
        ]);
    }

    /**
     * Show the form for creating a new attendance (manual).
     */
    public function create(): Response
    {
        $employees = Employee::active()
            ->select('id', 'first_name', 'last_name', 'employee_code', 'department_id')
            ->with('department:id,name')
            ->orderBy('first_name')
            ->get();

        $departments = Department::where('status', true)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return Inertia::render('employee::Dashboard/V1/Attendance/Create', [
            'employeeOptions' => $employees->map(fn ($e) => [
                'value' => $e->id,
                'label' => $e->full_name . ' (' . $e->employee_code . ')',
                'department' => $e->department?->name,
            ]),
            'departmentOptions' => $departments->map(fn ($d) => [
                'value' => $d->id,
                'label' => $d->name,
            ]),
            'statuses' => Attendance::getStatuses(),
        ]);
    }

    /**
     * Store a newly created attendance (manual).
     */
    public function store(StoreAttendanceRequest $request)
    {
        $attendance = $this->createAction->execute($request->validated());

        return redirect()
            ->route('employee.attendances.index')
            ->with('success', 'Attendance recorded successfully.');
    }

    /**
     * Display the specified attendance.
     */
    public function show(Attendance $attendance): Response
    {
        $data = $this->showAction->execute($attendance);

        return Inertia::render('employee::Dashboard/V1/Attendance/Show', [
            'attendance' => (new AttendanceResource($data['attendance']))->toArray(request()),
            'statuses' => $data['statuses'],
            'methods' => $data['methods'],
        ]);
    }

    /**
     * Show the form for editing the specified attendance.
     */
    public function edit(Attendance $attendance): Response
    {
        $attendance->load(['employee.user', 'department', 'classroom']);

        return Inertia::render('employee::Dashboard/V1/Attendance/Edit', [
            'attendance' => (new AttendanceResource($attendance))->toArray(request()),
            'statuses' => Attendance::getStatuses(),
        ]);
    }

    /**
     * Update the specified attendance.
     */
    public function update(UpdateAttendanceRequest $request, Attendance $attendance)
    {
        $this->updateAction->execute($attendance, $request->validated());

        return redirect()
            ->route('employee.attendances.index')
            ->with('success', 'Attendance updated successfully.');
    }

    /**
     * Remove the specified attendance.
     */
    public function destroy(Attendance $attendance)
    {
        $attendance->delete();

        return redirect()
            ->route('employee.attendances.index')
            ->with('success', 'Attendance record deleted successfully.');
    }

    /**
     * Show bulk delete confirmation modal.
     */
    public function confirmBulkDelete(Request $request): Modal
    {
        $uuids = $request->input('uuids', []);

        $attendances = Attendance::whereIn('uuid', $uuids)->with('employee')->get();

        return Inertia::modal('employee::Dashboard/V1/Attendance/BulkDelete', [
            'attendances' => AttendanceResource::collection($attendances)->resolve(),
        ])->baseRoute('employee.attendances.index');
    }

    /**
     * Bulk delete attendances.
     */
    public function bulkDelete(BulkDeleteAttendancesRequest $request): RedirectResponse
    {
        $result = $this->bulkDeleteAction->execute($request->validated('uuids'));

        $message = "{$result['deleted']} attendance record(s) deleted successfully.";

        if ($result['failed'] > 0) {
            $message .= " {$result['failed']} record(s) could not be found.";
        }

        return redirect()
            ->route('employee.attendances.index')
            ->with('success', $message);
    }

    /**
     * Show QR Scanner page.
     */
    public function scanner(): Response
    {
        $departments = Department::where('status', true)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $classrooms = Classroom::where('status', true)
            ->select('id', 'name', 'department_id')
            ->with('department:id,name')
            ->orderBy('name')
            ->get();

        return Inertia::render('employee::Dashboard/V1/Attendance/Scanner', [
            'departmentOptions' => $departments->map(fn ($d) => [
                'value' => $d->id,
                'label' => $d->name,
            ]),
            'classroomOptions' => $classrooms->map(fn ($c) => [
                'value' => $c->id,
                'label' => $c->name . ' (' . ($c->department?->name ?? 'N/A') . ')',
                'department_id' => $c->department_id,
            ]),
        ]);
    }

    /**
     * Process QR scan (API endpoint).
     */
    public function processScan(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
            'scan_type' => 'required|in:check_in,check_out',
            'location_type' => 'nullable|in:department,classroom',
            'location_id' => 'nullable|integer',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
            'timezone' => 'nullable|string|max:50',
            'device_info' => 'nullable|array',
            'device_info.browser' => 'nullable|string',
            'device_info.os' => 'nullable|string',
            'device_info.device_type' => 'nullable|string',
            'device_info.user_agent' => 'nullable|string',
        ]);

        $result = $this->qrScanAction->execute([
            'qr_code' => $request->qr_code,
            'scan_type' => $request->scan_type,
            'location_type' => $request->location_type ?? 'department',
            'location_id' => $request->location_id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'accuracy' => $request->accuracy,
            'timezone' => $request->timezone,
            'device_info' => $request->device_info ?? ['user_agent' => $request->header('User-Agent')],
            'ip_address' => $request->ip(),
        ]);

        return response()->json($result);
    }

    /**
     * Generate QR code for employee.
     */
    public function generateEmployeeQr(Employee $employee)
    {
        $result = $this->generateQrAction->generateEmployeeQr($employee);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Generate QR code for department.
     */
    public function generateDepartmentQr(Department $department)
    {
        $result = $this->generateQrAction->generateDepartmentQr($department);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Generate QR code for classroom.
     */
    public function generateClassroomQr(Classroom $classroom)
    {
        $result = $this->generateQrAction->generateClassroomQr($classroom);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Display attendance analytics dashboard.
     */
    public function analytics(Request $request): Response
    {
        $filters = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'department_id' => $request->input('department_id'),
            'employee_id' => $request->input('employee_id'),
        ];

        $data = $this->analyticsAction->execute($filters);

        $departments = Department::where('status', true)
            ->select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn ($d) => [
                'value' => $d->id,
                'label' => $d->name,
            ]);

        $employees = Employee::active()
            ->select('id', 'first_name', 'last_name', 'employee_code')
            ->orderBy('first_name')
            ->get()
            ->map(fn ($e) => [
                'value' => $e->id,
                'label' => $e->full_name . ' (' . $e->employee_code . ')',
            ]);

        return Inertia::render('employee::Dashboard/V1/Attendance/Analytics', [
            'analytics' => $data,
            'departmentOptions' => $departments,
            'employeeOptions' => $employees,
        ]);
    }

    /**
     * Get today's attendance summary.
     */
    public function todaySummary()
    {
        $today = today();

        $stats = [
            'total_employees' => Employee::active()->count(),
            'checked_in' => Attendance::whereDate('attendance_date', $today)
                ->whereNotNull('check_in_time')
                ->count(),
            'checked_out' => Attendance::whereDate('attendance_date', $today)
                ->whereNotNull('check_out_time')
                ->count(),
            'present' => Attendance::whereDate('attendance_date', $today)
                ->where('status', Attendance::STATUS_PRESENT)
                ->count(),
            'late' => Attendance::whereDate('attendance_date', $today)
                ->where('status', Attendance::STATUS_LATE)
                ->count(),
            'absent' => Attendance::whereDate('attendance_date', $today)
                ->where('status', Attendance::STATUS_ABSENT)
                ->count(),
            'on_leave' => Attendance::whereDate('attendance_date', $today)
                ->where('status', Attendance::STATUS_ON_LEAVE)
                ->count(),
        ];

        $recentScans = Attendance::with([
                'employee:id,first_name,last_name,avatar_url,employee_code,department_id',
                'employee.department:id,name',
                'department:id,name',
            ])
            ->whereDate('attendance_date', $today)
            ->latest('updated_at')
            ->limit(10)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'employee' => [
                    'name' => $a->employee->full_name,
                    'code' => $a->employee->employee_code,
                    'avatar' => $a->employee->avatar_url,
                    'department' => $a->employee->department?->name ?? $a->department?->name,
                ],
                'department_name' => $a->department?->name ?? $a->employee->department?->name,
                'check_in' => $a->check_in_time?->format('H:i'),
                'check_out' => $a->check_out_time?->format('H:i'),
                'status' => $a->status,
            ]);

        return response()->json([
            'stats' => $stats,
            'recent_scans' => $recentScans,
        ]);
    }
}
