<?php

namespace Modules\Employee\Http\Resources\Dashboard\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Employee\Models\Attendance;

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

            // Employee info
            'employee_id' => $this->employee_id,
            'employee_name' => $this->employee?->full_name,
            'employee_code' => $this->employee?->employee_code,
            'employee_avatar' => $this->employee?->avatar_url,
            'employee_email' => $this->employee?->email,
            'employee_phone' => $this->employee?->phone_number,
            'employee_job_title' => $this->employee?->job_title,
            'employee_department' => $this->employee?->department?->name,

            // User info (linked user account)
            'user_id' => $this->employee?->user_id,
            'user_name' => $this->employee?->user?->name,
            'user_email' => $this->employee?->user?->email,

            // Location info
            'school_id' => $this->school_id,
            'school_name' => $this->school?->name,
            'department_id' => $this->department_id,
            'department_name' => $this->department?->name,
            'classroom_id' => $this->classroom_id,
            'classroom_name' => $this->classroom?->name,

            // Attendance details
            'attendance_date' => $this->attendance_date?->format('Y-m-d'),
            'attendance_date_formatted' => $this->attendance_date?->format('M d, Y'),
            'check_in_time' => $this->check_in_time?->format('H:i'),
            'check_out_time' => $this->check_out_time?->format('H:i'),
            'status' => $this->status,
            'status_label' => Attendance::getStatuses()[$this->status] ?? $this->status,
            'check_in_method' => $this->check_in_method,
            'check_in_method_label' => Attendance::getMethods()[$this->check_in_method] ?? $this->check_in_method,
            'check_out_method' => $this->check_out_method,
            'check_out_method_label' => $this->check_out_method
                ? (Attendance::getMethods()[$this->check_out_method] ?? $this->check_out_method)
                : null,

            // Location tracking
            'check_in_location' => $this->check_in_location,
            'check_out_location' => $this->check_out_location,
            'check_in_coordinates' => $this->check_in_latitude && $this->check_in_longitude
                ? ['lat' => $this->check_in_latitude, 'lng' => $this->check_in_longitude]
                : null,
            'check_out_coordinates' => $this->check_out_latitude && $this->check_out_longitude
                ? ['lat' => $this->check_out_latitude, 'lng' => $this->check_out_longitude]
                : null,

            // Work hours
            'work_hours' => $this->work_hours,
            'work_hours_formatted' => $this->getFormattedWorkHours(),
            'overtime_hours' => $this->overtime_hours,

            // Additional info
            'notes' => $this->notes,
            'device_info' => $this->device_info,
            'ip_address' => $this->ip_address,

            // Timestamps
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
