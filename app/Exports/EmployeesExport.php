<?php

namespace Modules\Employee\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Modules\Employee\Models\Employee;
use Illuminate\Database\Eloquent\Builder;

class EmployeesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Build the query for export.
     */
    public function query(): Builder
    {
        $query = Employee::query()
            ->with(['school', 'department', 'employeeType'])
            ->latest();

        // Apply filters
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        if (isset($this->filters['status']) && $this->filters['status'] !== 'all') {
            $status = filter_var($this->filters['status'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($status !== null) {
                $query->where('status', $status);
            }
        }

        if (!empty($this->filters['employee_type']) && $this->filters['employee_type'] !== 'all') {
            $query->where('employee_type', $this->filters['employee_type']);
        }

        if (!empty($this->filters['school_id']) && $this->filters['school_id'] !== 'all') {
            $query->where('school_id', $this->filters['school_id']);
        }

        if (!empty($this->filters['department_id']) && $this->filters['department_id'] !== 'all') {
            $query->where('department_id', $this->filters['department_id']);
        }

        return $query;
    }

    /**
     * Column headings.
     */
    public function headings(): array
    {
        return [
            'Employee Code',
            'First Name',
            'Last Name',
            'Email',
            'Phone Number',
            'Gender',
            'Date of Birth',
            'Birth Place',
            'Current Address',
            'School',
            'Department',
            'Employee Type',
            'Job Title',
            'Employment Type',
            'Salary',
            'Hire Date',
            'Probation Date',
            'Probation End Date',
            'Status',
            'Created At',
        ];
    }

    /**
     * Map each row for export.
     */
    public function map($employee): array
    {
        return [
            $employee->employee_code,
            $employee->first_name,
            $employee->last_name,
            $employee->email,
            $employee->phone_number,
            $employee->gender ? ucfirst($employee->gender) : '',
            $employee->date_of_birth?->format('Y-m-d'),
            $employee->birth_place,
            $employee->current_address,
            $employee->school?->name,
            $employee->department?->name,
            $employee->employeeType?->name,
            $employee->job_title,
            $this->formatEmployeeType($employee->employee_type),
            $employee->salary,
            $employee->hire_date?->format('Y-m-d'),
            $employee->probation_date?->format('Y-m-d'),
            $employee->probation_end_date?->format('Y-m-d'),
            $employee->status ? 'Active' : 'Inactive',
            $employee->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Style the worksheet.
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            // Style the header row
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F46E5'],
                ],
            ],
        ];
    }

    /**
     * Format employee type for display.
     */
    protected function formatEmployeeType(?string $type): string
    {
        return match ($type) {
            'full_time' => 'Full Time',
            'part_time' => 'Part Time',
            'contract' => 'Contract',
            'intern' => 'Intern',
            default => $type ?? '',
        };
    }
}
