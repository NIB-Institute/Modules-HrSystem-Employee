<?php

namespace Modules\Employee\Actions\Api\V1\Attendance;

use App\Models\User;
use Modules\Employee\Http\Resources\Api\V1\AttendanceResource;
use Modules\Employee\Models\Attendance;
use Modules\Employee\Models\AttendanceScan;
use Modules\Employee\Models\Employee;

class CheckOutAction
{
    /**
     * Execute check-out action.
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

        // Get today's attendance
        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('attendance_date', today())
            ->first();

        if (!$attendance) {
            return [
                'success' => false,
                'message' => 'You have not checked in today',
            ];
        }

        if (!$attendance->hasCheckedIn()) {
            return [
                'success' => false,
                'message' => 'You must check in before checking out',
            ];
        }

        if ($attendance->hasCheckedOut()) {
            return [
                'success' => false,
                'message' => 'You have already checked out today',
                'attendance' => new AttendanceResource($attendance->load('scans')),
            ];
        }

        // Verify geofence if department has location
        $geofenceResult = $this->verifyGeofence($employee, $data);

        // Update check-out info
        $attendance->update([
            'check_out_time' => now(),
            'check_out_method' => $data['scan_method'] ?? Attendance::METHOD_QR_SCAN,
            'check_out_latitude' => $data['latitude'] ?? null,
            'check_out_longitude' => $data['longitude'] ?? null,
            'check_out_location' => $data['address'] ?? null,
        ]);

        // Create scan record
        AttendanceScan::create([
            'attendance_id' => $attendance->id,
            'scan_type' => AttendanceScan::TYPE_CHECK_OUT,
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
            'message' => 'Check-out successful',
            'work_hours' => $attendance->fresh()->getFormattedWorkHours(),
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
}
