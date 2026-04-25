<?php

namespace Modules\Employee\Actions\Dashboard\V1\Attendance;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Employee\Models\Attendance;
use Modules\Employee\Models\AttendanceScan;
use Modules\Employee\Models\Employee;
use Modules\School\Models\Department;
use Modules\School\Models\Classroom;

class ProcessQrScanAction
{
    /**
     * Execute QR scan for attendance.
     *
     * Supports two modes:
     * 1. Location QR Scan: Employee scans a classroom/department QR to record attendance
     * 2. Employee QR Scan: System scans employee's QR at a location (legacy)
     */
    public function execute(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $qrCode = $data['qr_code'];
            $employeeId = $data['employee_id'] ?? null;

            // Try to parse QR code as JSON (classroom/department QR)
            $qrData = json_decode($qrCode, true);

            if (json_last_error() === JSON_ERROR_NONE && isset($qrData['type'])) {
                // This is a location QR code (classroom or department)
                return $this->processLocationQrScan($qrData, $data, $employeeId);
            }

            // Legacy: Employee QR code scan
            return $this->processEmployeeQrScan($data);
        });
    }

    /**
     * Process scanning a classroom/department QR code.
     * The logged-in employee scans the QR placed at a location.
     */
    private function processLocationQrScan(array $qrData, array $data, ?int $employeeId): array
    {
        // Validate employee
        $employee = null;
        if ($employeeId) {
            $employee = Employee::find($employeeId);
        } elseif (auth()->check()) {
            // Try to find employee linked to logged-in user
            $employee = Employee::where('user_id', auth()->id())->first();
        }

        if (!$employee) {
            return [
                'success' => false,
                'message' => 'Employee not identified. Please ensure you are logged in.',
                'data' => null,
            ];
        }

        if (!$employee->status) {
            return [
                'success' => false,
                'message' => 'Employee is inactive.',
                'data' => null,
            ];
        }

        // Determine location type and ID from QR data
        $locationType = $qrData['type']; // 'classroom' or 'department'
        $locationId = $qrData['classroom_id'] ?? $qrData['department_id'] ?? null;

        // Validate the location exists and get geofence settings
        $department = null;
        $geofenceVerification = null;

        if ($locationType === 'classroom') {
            $classroom = Classroom::find($locationId);
            if (!$classroom) {
                return [
                    'success' => false,
                    'message' => 'Invalid classroom QR code.',
                    'data' => null,
                ];
            }
            $locationName = $classroom->name . ' (' . ($classroom->department?->name ?? 'N/A') . ')';
            $departmentId = $classroom->department_id;
            $classroomId = $classroom->id;
            // Get department for geofence check
            $department = $classroom->department;
        } else {
            $department = Department::find($locationId);
            if (!$department) {
                return [
                    'success' => false,
                    'message' => 'Invalid department QR code.',
                    'data' => null,
                ];
            }
            $locationName = $department->name;
            $departmentId = $department->id;
            $classroomId = null;
        }

        // Verify geofence if department has it configured
        if ($department && $department->hasGeofence()) {
            $geofenceVerification = $this->verifyGeofence(
                $department,
                $data['latitude'] ?? null,
                $data['longitude'] ?? null
            );

            // If geofence enforcement is on and employee is outside, block the scan
            if ($department->enforce_geofence && !$geofenceVerification['is_within']) {
                return [
                    'success' => false,
                    'message' => $geofenceVerification['message'],
                    'geofence_blocked' => true,
                    'data' => [
                        'employee' => $this->formatEmployeeData($employee),
                        'geofence' => $geofenceVerification,
                    ],
                ];
            }
        }

        $today = today();
        $now = now();

        // Find today's attendance
        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('attendance_date', $today)
            ->first();

        // Smart check-in/check-out: if no attendance or no check-in, do check-in. If checked in but not out, do check-out.
        if (!$attendance || !$attendance->check_in_time) {
            $result = $this->processCheckIn(
                $employee,
                $attendance,
                $data,
                $today,
                $now,
                $locationType,
                $locationId,
                $locationName,
                $departmentId,
                $classroomId,
                $geofenceVerification
            );
        } elseif (!$attendance->check_out_time) {
            $result = $this->processCheckOut(
                $employee,
                $attendance,
                $data,
                $now,
                $locationType,
                $locationId,
                $locationName,
                $geofenceVerification
            );
        } else {
            return [
                'success' => false,
                'message' => 'Already checked in at ' . $attendance->check_in_time->format('H:i') .
                    ' and checked out at ' . $attendance->check_out_time->format('H:i') . '.',
                'data' => [
                    'employee' => $this->formatEmployeeData($employee),
                    'attendance' => $attendance,
                    'geofence' => $geofenceVerification,
                ],
            ];
        }

        // Add geofence verification to successful result
        if ($geofenceVerification) {
            $result['data']['geofence'] = $geofenceVerification;
        }

        return $result;
    }

    /**
     * Legacy: Process scanning an employee's QR code.
     */
    private function processEmployeeQrScan(array $data): array
    {
        $scanType = $data['scan_type'] ?? 'check_in';
        $qrCode = $data['qr_code'];
        $locationType = $data['location_type'] ?? 'department';
        $locationId = $data['location_id'] ?? null;

        // Find employee by QR code
        $employee = Employee::where('employee_qr_code', $qrCode)
            ->orWhere('uuid', $qrCode)
            ->orWhere('employee_code', $qrCode)
            ->first();

        if (!$employee) {
            return [
                'success' => false,
                'message' => 'Employee not found. Invalid QR code.',
                'data' => null,
            ];
        }

        if (!$employee->status) {
            return [
                'success' => false,
                'message' => 'Employee is inactive.',
                'data' => null,
            ];
        }

        $today = today();
        $now = now();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('attendance_date', $today)
            ->first();

        // Get location data for legacy mode
        $locationData = $this->getLocationData($locationType, $locationId, $employee);

        if ($scanType === 'check_in') {
            return $this->processCheckIn(
                $employee,
                $attendance,
                $data,
                $today,
                $now,
                $locationType,
                $locationId,
                $locationData['location_name'],
                $locationData['department_id'],
                $locationData['classroom_id']
            );
        } else {
            return $this->processCheckOut(
                $employee,
                $attendance,
                $data,
                $now,
                $locationType,
                $locationId,
                $locationData['location_name']
            );
        }
    }

    private function processCheckIn(
        Employee $employee,
        ?Attendance $attendance,
        array $data,
        $today,
        $now,
        string $locationType,
        ?int $locationId,
        ?string $locationName = null,
        ?int $departmentId = null,
        ?int $classroomId = null,
        ?array $geofenceVerification = null
    ): array {
        if ($attendance && $attendance->check_in_time) {
            return [
                'success' => false,
                'message' => 'Already checked in today at ' . $attendance->check_in_time->format('H:i'),
                'data' => [
                    'employee' => $this->formatEmployeeData($employee),
                    'attendance' => $attendance,
                ],
            ];
        }

        // Determine status based on check-in time
        $status = $this->determineCheckInStatus($now);

        // Use provided location data or fallback to employee's default
        $finalDepartmentId = $departmentId ?? $employee->department_id;
        $finalClassroomId = $classroomId;
        $finalLocationName = $locationName ?? $employee->department?->name ?? 'Unknown';

        $attendanceData = [
            'employee_id' => $employee->id,
            'school_id' => $employee->school_id,
            'department_id' => $finalDepartmentId,
            'classroom_id' => $finalClassroomId,
            'attendance_date' => $today,
            'check_in_time' => $now->format('H:i:s'),
            'status' => $status,
            'check_in_method' => Attendance::METHOD_QR_SCAN,
            'check_in_location' => $finalLocationName,
            'check_in_latitude' => $data['latitude'] ?? null,
            'check_in_longitude' => $data['longitude'] ?? null,
            'device_info' => $data['device_info'] ?? null,
            'ip_address' => $data['ip_address'] ?? null,
        ];

        if ($attendance) {
            $attendance->update($attendanceData);
        } else {
            $attendance = Attendance::create($attendanceData);
        }

        // Create AttendanceScan record for check-in with geofence data
        $this->createAttendanceScan(
            $attendance,
            AttendanceScan::TYPE_CHECK_IN,
            $data,
            $locationType,
            $locationId,
            $geofenceVerification
        );

        $actionType = $locationType === 'classroom' ? 'Classroom' : 'Department';

        return [
            'success' => true,
            'message' => "Check-in successful at {$finalLocationName} ({$now->format('H:i')})",
            'action' => 'check_in',
            'data' => [
                'employee' => $this->formatEmployeeData($employee),
                'attendance' => $attendance->fresh(['employee', 'department', 'classroom', 'scans']),
                'status' => $status,
                'location_type' => $locationType,
                'location_name' => $finalLocationName,
            ],
        ];
    }

    private function processCheckOut(
        Employee $employee,
        ?Attendance $attendance,
        array $data,
        $now,
        string $locationType,
        ?int $locationId,
        ?string $locationName = null,
        ?array $geofenceVerification = null
    ): array {
        if (!$attendance || !$attendance->check_in_time) {
            return [
                'success' => false,
                'message' => 'No check-in record found for today. Please check in first.',
                'data' => [
                    'employee' => $this->formatEmployeeData($employee),
                ],
            ];
        }

        if ($attendance->check_out_time) {
            return [
                'success' => false,
                'message' => 'Already checked out today at ' . $attendance->check_out_time->format('H:i'),
                'data' => [
                    'employee' => $this->formatEmployeeData($employee),
                    'attendance' => $attendance,
                ],
            ];
        }

        $finalLocationName = $locationName ?? $attendance->check_in_location ?? 'Unknown';

        // Update status if early leave
        $status = $this->determineCheckOutStatus($attendance->status, $now);

        $attendance->update([
            'check_out_time' => $now->format('H:i:s'),
            'status' => $status,
            'check_out_method' => Attendance::METHOD_QR_SCAN,
            'check_out_location' => $finalLocationName,
            'check_out_latitude' => $data['latitude'] ?? null,
            'check_out_longitude' => $data['longitude'] ?? null,
        ]);

        // Create AttendanceScan record for check-out with geofence data
        $this->createAttendanceScan(
            $attendance,
            AttendanceScan::TYPE_CHECK_OUT,
            $data,
            $locationType,
            $locationId,
            $geofenceVerification
        );

        $workHours = $attendance->fresh()->getFormattedWorkHours();

        return [
            'success' => true,
            'message' => "Check-out successful from {$finalLocationName} ({$now->format('H:i')}). Work hours: {$workHours}",
            'action' => 'check_out',
            'data' => [
                'employee' => $this->formatEmployeeData($employee),
                'attendance' => $attendance->fresh(['employee', 'department', 'classroom', 'scans']),
                'work_hours' => $attendance->fresh()->work_hours,
                'location_type' => $locationType,
                'location_name' => $finalLocationName,
            ],
        ];
    }

    private function determineCheckInStatus($checkInTime): string
    {
        // Define work start time (configurable)
        $workStartTime = config('employee.work_start_time', '08:00');
        $lateThreshold = config('employee.late_threshold_minutes', 15);

        $workStart = \Carbon\Carbon::parse($workStartTime);
        $lateTime = $workStart->copy()->addMinutes($lateThreshold);

        if ($checkInTime->gt($lateTime)) {
            return Attendance::STATUS_LATE;
        }

        return Attendance::STATUS_PRESENT;
    }

    private function determineCheckOutStatus(string $currentStatus, $checkOutTime): string
    {
        // Define work end time (configurable)
        $workEndTime = config('employee.work_end_time', '17:00');
        $earlyLeaveThreshold = config('employee.early_leave_threshold_minutes', 30);

        $workEnd = \Carbon\Carbon::parse($workEndTime);
        $earlyLeaveTime = $workEnd->copy()->subMinutes($earlyLeaveThreshold);

        if ($checkOutTime->lt($earlyLeaveTime) && $currentStatus !== Attendance::STATUS_LATE) {
            return Attendance::STATUS_EARLY_LEAVE;
        }

        // If was late, keep that status
        return $currentStatus;
    }

    private function getLocationData(string $locationType, ?int $locationId, Employee $employee): array
    {
        $data = [
            'department_id' => $employee->department_id,
            'classroom_id' => null,
            'location_name' => null,
        ];

        if ($locationType === 'department' && $locationId) {
            $department = Department::find($locationId);
            if ($department) {
                $data['department_id'] = $department->id;
                $data['location_name'] = $department->name;
            }
        } elseif ($locationType === 'classroom' && $locationId) {
            $classroom = Classroom::with('department')->find($locationId);
            if ($classroom) {
                $data['classroom_id'] = $classroom->id;
                $data['department_id'] = $classroom->department_id;
                $data['location_name'] = $classroom->name . ' (' . ($classroom->department->name ?? 'N/A') . ')';
            }
        }

        // Fallback to employee's department
        if (!$data['location_name'] && $employee->department) {
            $data['location_name'] = $employee->department->name;
        }

        return $data;
    }

    private function formatEmployeeData(Employee $employee): array
    {
        return [
            'id' => $employee->id,
            'uuid' => $employee->uuid,
            'employee_code' => $employee->employee_code,
            'full_name' => $employee->full_name,
            'avatar_url' => $employee->avatar_url,
            'department' => $employee->department?->name,
            'job_title' => $employee->job_title,
        ];
    }

    /**
     * Create an AttendanceScan record with GPS, device info, and geofence verification.
     */
    private function createAttendanceScan(
        Attendance $attendance,
        string $scanType,
        array $data,
        string $locationType,
        ?int $locationId,
        ?array $geofenceVerification = null
    ): AttendanceScan {
        $scanData = [
            'attendance_id' => $attendance->id,
            'scan_type' => $scanType,
            'scanned_at' => now(),
            'timezone' => $data['timezone'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'accuracy' => $data['accuracy'] ?? null,
            'scan_method' => AttendanceScan::METHOD_QR_SCAN,
            'device_info' => $data['device_info'] ?? null,
            'ip_address' => $data['ip_address'] ?? null,
            'location_type' => $locationType,
            'location_id' => $locationId,
            'is_verified' => true,
        ];

        // Add geofence verification data if available
        if ($geofenceVerification) {
            $scanData['within_geofence'] = $geofenceVerification['is_within'] ?? false;
            $scanData['distance_from_location'] = $geofenceVerification['distance'] ?? null;
            $scanData['verification_status'] = $geofenceVerification['status'] ?? 'no_location';
            $scanData['verification_note'] = $geofenceVerification['message'] ?? null;

            // Link to Location model if available
            if (isset($geofenceVerification['location_data']['id'])) {
                $scanData['designated_location_id'] = $geofenceVerification['location_data']['id'];
            }
        }

        return AttendanceScan::create($scanData);
    }

    /**
     * Verify if employee's GPS location is within department's geofence.
     *
     * Uses the linked Location model for comprehensive geofence verification.
     * Supports circle, polygon, and dynamic geofence types.
     */
    private function verifyGeofence(Department $department, ?float $latitude, ?float $longitude): array
    {
        // Load location relationship if not already loaded
        $department->loadMissing('location');

        // Use Location's verifyLocation method if linked
        $location = $department->location;

        if ($location) {
            $verification = $location->verifyLocation($latitude, $longitude);

            // Format distance
            $distance = $verification['distance_meters'] ?? null;
            $distanceFormatted = null;
            if ($distance !== null) {
                $distanceFormatted = $distance < 1000
                    ? round($distance) . 'm'
                    : round($distance / 1000, 2) . 'km';
            }

            return [
                'is_within' => $verification['within_geofence'],
                'verified' => $verification['verified'],
                'has_location' => $latitude !== null && $longitude !== null,
                'distance' => $distance,
                'distance_formatted' => $distanceFormatted,
                'radius' => $verification['geofence_radius'] ?? $location->geofence_radius,
                'geofence_type' => $verification['geofence_type'] ?? $location->geofence_type,
                'enforce' => $location->enforce_geofence,
                'location_data' => [
                    'id' => $location->id,
                    'name' => $location->name,
                    'latitude' => (float) $location->latitude,
                    'longitude' => (float) $location->longitude,
                ],
                'employee_location' => $latitude !== null ? [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ] : null,
                'message' => $verification['message'],
                'status' => $verification['within_geofence'] ? 'verified' : ($latitude === null ? 'no_location' : 'outside_geofence'),
            ];
        }

        // Fallback to Department's direct geofence fields (backwards compatibility)
        if ($latitude === null || $longitude === null) {
            return [
                'is_within' => false,
                'verified' => !$department->enforce_geofence,
                'has_location' => false,
                'distance' => null,
                'radius' => $department->geofence_radius,
                'geofence_type' => 'circle',
                'enforce' => $department->enforce_geofence,
                'location_data' => [
                    'latitude' => $department->latitude,
                    'longitude' => $department->longitude,
                ],
                'message' => 'Location data not available. Please enable GPS.',
                'status' => 'no_location',
            ];
        }

        // Use Department's isWithinGeofence method
        $result = $department->isWithinGeofence($latitude, $longitude);
        $distance = $result['distance_meters'] ?? null;
        $distanceFormatted = null;

        if ($distance !== null) {
            $distanceFormatted = $distance < 1000
                ? round($distance) . 'm'
                : round($distance / 1000, 2) . 'km';
        }

        return [
            'is_within' => $result['within_geofence'] ?? false,
            'verified' => $result['verified'] ?? false,
            'has_location' => true,
            'distance' => $distance,
            'distance_formatted' => $distanceFormatted,
            'radius' => $result['geofence_radius'] ?? $department->geofence_radius,
            'geofence_type' => $result['geofence_type'] ?? 'circle',
            'enforce' => $department->enforce_geofence,
            'location_data' => [
                'latitude' => $department->latitude,
                'longitude' => $department->longitude,
            ],
            'employee_location' => [
                'latitude' => $latitude,
                'longitude' => $longitude,
            ],
            'message' => $result['message'] ?? ($result['within_geofence']
                ? "Location verified ({$distanceFormatted} from center)"
                : "You are {$distanceFormatted} away from the allowed area (max {$department->geofence_radius}m)"),
            'status' => $result['within_geofence'] ? 'verified' : 'outside_geofence',
        ];
    }
}
