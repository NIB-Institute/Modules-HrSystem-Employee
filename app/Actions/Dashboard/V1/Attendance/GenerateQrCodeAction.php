<?php

namespace Modules\Employee\Actions\Dashboard\V1\Attendance;

use Illuminate\Support\Str;
use Modules\Employee\Models\Employee;
use Modules\School\Models\Department;
use Modules\School\Models\Classroom;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GenerateQrCodeAction
{
    /**
     * Generate QR code for an employee.
     */
    public function generateEmployeeQr(Employee $employee): array
    {
        // Generate unique QR code if not exists
        if (empty($employee->employee_qr_code)) {
            $qrCode = 'EMP-' . strtoupper(Str::random(8)) . '-' . $employee->id;
            $employee->update(['employee_qr_code' => $qrCode]);
        }

        $qrData = json_encode([
            'type' => 'employee',
            'code' => $employee->employee_qr_code,
            'employee_id' => $employee->id,
        ]);

        return [
            'qr_code' => $employee->employee_qr_code,
            'qr_data' => $qrData,
            'qr_image' => $this->generateQrImage($qrData),
        ];
    }

    /**
     * Generate QR code for a department (location-based check-in).
     */
    public function generateDepartmentQr(Department $department): array
    {
        $qrData = json_encode([
            'type' => 'department',
            'department_id' => $department->id,
            'name' => $department->name,
            'scan_type' => 'location',
        ]);

        return [
            'location_type' => 'department',
            'location_id' => $department->id,
            'location_name' => $department->name,
            'qr_data' => $qrData,
            'qr_image' => $this->generateQrImage($qrData),
        ];
    }

    /**
     * Generate QR code for a classroom (location-based check-in).
     */
    public function generateClassroomQr(Classroom $classroom): array
    {
        $qrData = json_encode([
            'type' => 'classroom',
            'classroom_id' => $classroom->id,
            'name' => $classroom->name,
            'department_id' => $classroom->department_id,
            'scan_type' => 'location',
        ]);

        return [
            'location_type' => 'classroom',
            'location_id' => $classroom->id,
            'location_name' => $classroom->name,
            'qr_data' => $qrData,
            'qr_image' => $this->generateQrImage($qrData),
        ];
    }

    /**
     * Generate QR image as base64 or SVG.
     */
    private function generateQrImage(string $data, string $format = 'svg'): string
    {
        // Check if QrCode facade exists
        if (class_exists(QrCode::class)) {
            if ($format === 'svg') {
                return QrCode::size(300)->generate($data);
            }

            return 'data:image/png;base64,' . base64_encode(
                QrCode::format('png')->size(300)->generate($data)
            );
        }

        // Fallback: Return data URL for client-side QR generation
        return 'generate_client_side:' . base64_encode($data);
    }

    /**
     * Regenerate QR code for an employee.
     */
    public function regenerateEmployeeQr(Employee $employee): array
    {
        $qrCode = 'EMP-' . strtoupper(Str::random(8)) . '-' . $employee->id;
        $employee->update(['employee_qr_code' => $qrCode]);

        return $this->generateEmployeeQr($employee->fresh());
    }
}
