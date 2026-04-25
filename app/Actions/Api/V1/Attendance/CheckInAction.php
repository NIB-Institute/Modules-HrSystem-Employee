<?php

namespace Modules\Employee\Actions\Api\V1\Attendance;

use App\Models\User;
use Modules\Employee\Http\Resources\Api\V1\AttendanceResource;
use Modules\Employee\Models\Attendance;
use Modules\Employee\Models\AttendanceScan;
use Modules\Employee\Models\Employee;
use Modules\School\Models\Department;

class CheckInAction
{
    /**
     * Execute check-in action.
     */
    public function execute(User $user, array $data): array
    {
        $employee = Employee::with(['school', 'department'])->where('user_id', $user->id)->first();

        if (!$employee) {
            return [
                'success' => false,
                'message' => 'Employee not found',
            ];
        }

        // Check if already checked in today
        $existingAttendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('attendance_date', today())
            ->first();

        if ($existingAttendance && $existingAttendance->hasCheckedIn()) {
            return [
                'success' => false,
                'message' => 'You have already checked in today',
                'attendance' => new AttendanceResource($existingAttendance->load('scans')),
            ];
        }

        // Verify geofence if department has location
        $geofenceResult = $this->verifyGeofence($employee, $data);

        // Create or update attendance record
        $attendance = $existingAttendance ?? Attendance::create([
            'employee_id' => $employee->id,
            'school_id' => $employee->school_id,
            'department_id' => $employee->department_id,
            'attendance_date' => today(),
            'status' => $this->determineStatus(),
        ]);

        // Update check-in info
        $attendance->update([
            'check_in_time' => now(),
            'check_in_method' => $data['scan_method'] ?? Attendance::METHOD_QR_SCAN,
            'check_in_latitude' => $data['latitude'] ?? null,
            'check_in_longitude' => $data['longitude'] ?? null,
            'check_in_location' => $data['address'] ?? null,
            'device_info' => $data['device_info'] ?? null,
            'ip_address' => request()->ip(),
        ]);

        // Create scan record
        AttendanceScan::create([
            'attendance_id' => $attendance->id,
            'scan_type' => AttendanceScan::TYPE_CHECK_IN,
            'scanned_at' => now(),
            'timezone' => $data['timezone'] ?? config('app.timezone'),
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'accuracy' => $data['accuracy'] ?? null,
            'address' => $data['address'] ?? null,
            'scan_method' => $data['scan_method'] ?? AttendanceScan::METHOD_QR_SCAN,
            'device_info' => $data['device_info'] ?? null,
            'ip_address' => request()->ip(),
            'is_verified' => $geofenceResult['verified'],
            'within_geofence' => $geofenceResult['within_geofence'],
            'distance_from_location' => $geofenceResult['distance_meters'],
            'verification_status' => $geofenceResult['verified']
                ? AttendanceScan::STATUS_VERIFIED
                : ($geofenceResult['within_geofence'] === false
                    ? AttendanceScan::STATUS_OUTSIDE_GEOFENCE
                    : AttendanceScan::STATUS_NO_LOCATION),
            'verification_note' => $geofenceResult['message'],
        ]);

        return [
            'success' => true,
            'message' => 'Check-in successful',
            'geofence' => $geofenceResult,
            'attendance' => new AttendanceResource($attendance->fresh()->load('scans')),
        ];
    }

    /**
     * Verify employee location against department geofence.
     */
    private function verifyGeofence(Employee $employee, array $data): array
    {
        if (!$employee->department) {
            return [
                'verified' => true,
                'within_geofence' => null,
                'distance_meters' => null,
                'message' => 'No department assigned',
            ];
        }

        $lat = $data['latitude'] ?? null;
        $lng = $data['longitude'] ?? null;

        return $employee->department->isWithinGeofence($lat, $lng);
    }

    /**
     * Determine attendance status based on time.
     */
    private function determineStatus(): string
    {
        // TODO: Compare with schedule to determine late/on-time
        $hour = now()->hour;

        if ($hour >= 9) {
            return Attendance::STATUS_LATE;
        }

        return Attendance::STATUS_PRESENT;
    }
}
