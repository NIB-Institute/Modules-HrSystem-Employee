<?php

namespace Modules\Employee\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'attendance_date' => $this->attendance_date?->format('Y-m-d'),
            'check_in_time' => $this->check_in_time?->format('H:i:s'),
            'check_out_time' => $this->check_out_time?->format('H:i:s'),
            'status' => $this->status,
            'status_label' => $this->getStatuses()[$this->status] ?? $this->status,
            'check_in_method' => $this->check_in_method,
            'check_out_method' => $this->check_out_method,
            'check_in_location' => $this->check_in_location,
            'check_out_location' => $this->check_out_location,
            'check_in_latitude' => $this->check_in_latitude,
            'check_in_longitude' => $this->check_in_longitude,
            'check_out_latitude' => $this->check_out_latitude,
            'check_out_longitude' => $this->check_out_longitude,
            'work_hours' => $this->work_hours,
            'work_hours_formatted' => $this->getFormattedWorkHours(),
            'overtime_hours' => $this->overtime_hours,
            'notes' => $this->notes,
            'has_checked_in' => $this->hasCheckedIn(),
            'has_checked_out' => $this->hasCheckedOut(),

            // Relationships
            'department' => $this->whenLoaded('department', fn () => [
                'id' => $this->department->id,
                'name' => $this->department->name,
            ]),
            'school' => $this->whenLoaded('school', fn () => [
                'id' => $this->school->id,
                'name' => $this->school->name,
            ]),
            'scans' => $this->whenLoaded('scans', fn () =>
                $this->scans->map(fn ($scan) => [
                    'id' => $scan->id,
                    'uuid' => $scan->uuid,
                    'scan_type' => $scan->scan_type,
                    'scanned_at' => $scan->scanned_at?->toISOString(),
                    'latitude' => $scan->latitude,
                    'longitude' => $scan->longitude,
                    'address' => $scan->address,
                    'scan_method' => $scan->scan_method,
                    'is_verified' => $scan->is_verified,
                    'within_geofence' => $scan->within_geofence,
                    'distance_from_location' => $scan->distance_from_location,
                    'verification_status' => $scan->verification_status,
                    'verification_note' => $scan->verification_note,
                ])
            ),

            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
