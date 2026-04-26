<?php

namespace Modules\Employee\Exports;

use App\Concerns\Exports\HasSelectableColumns;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Modules\Employee\Models\Employee;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    use HasSelectableColumns;

    /**
     * @param  array<string, mixed>  $filters
     */
    public function __construct(protected array $filters = [])
    {
    }

    /**
     * Full set of exportable columns. Order here is the order written
     * to the spreadsheet (when not overridden by the user picker).
     *
     * @return array<string, array{label: string, value: callable, default?: bool}>
     */
    public function columnMap(): array
    {
        return [
            'employee_code' => [
                'label' => 'Employee Code',
                'value' => fn ($e) => $e->employee_code,
            ],
            'first_name' => [
                'label' => 'First Name',
                'value' => fn ($e) => $e->first_name,
            ],
            'last_name' => [
                'label' => 'Last Name',
                'value' => fn ($e) => $e->last_name,
            ],
            'email' => [
                'label' => 'Email',
                'value' => fn ($e) => $e->email,
            ],
            'phone_number' => [
                'label' => 'Phone Number',
                'value' => fn ($e) => $e->phone_number,
            ],
            'gender' => [
                'label' => 'Gender',
                'value' => fn ($e) => $e->gender ? ucfirst($e->gender) : '',
            ],
            'date_of_birth' => [
                'label' => 'Date of Birth',
                'value' => fn ($e) => $e->date_of_birth?->format('Y-m-d'),
            ],
            'birth_place' => [
                'label' => 'Birth Place',
                'value' => fn ($e) => $e->birth_place,
                'default' => false,
            ],
            'current_address' => [
                'label' => 'Current Address',
                'value' => fn ($e) => $e->current_address,
                'default' => false,
            ],
            'school' => [
                'label' => 'School',
                'value' => fn ($e) => $e->school?->name,
            ],
            'department' => [
                'label' => 'Department',
                'value' => fn ($e) => $e->department?->name,
            ],
            'employee_type_name' => [
                'label' => 'Employee Type',
                'value' => fn ($e) => $e->employeeType?->name,
            ],
            'job_title' => [
                'label' => 'Job Title',
                'value' => fn ($e) => $e->job_title,
            ],
            'employment_type' => [
                'label' => 'Employment Type',
                'value' => fn ($e) => $this->formatEmployeeType($e->employee_type),
            ],
            'salary' => [
                'label' => 'Salary',
                'value' => fn ($e) => $e->salary,
                'default' => false,
            ],
            'hire_date' => [
                'label' => 'Hire Date',
                'value' => fn ($e) => $e->hire_date?->format('Y-m-d'),
            ],
            'probation_date' => [
                'label' => 'Probation Date',
                'value' => fn ($e) => $e->probation_date?->format('Y-m-d'),
                'default' => false,
            ],
            'probation_end_date' => [
                'label' => 'Probation End Date',
                'value' => fn ($e) => $e->probation_end_date?->format('Y-m-d'),
                'default' => false,
            ],
            'status' => [
                'label' => 'Status',
                'value' => fn ($e) => $e->status ? 'Active' : 'Inactive',
            ],
            'created_at' => [
                'label' => 'Created At',
                'value' => fn ($e) => $e->created_at?->format('Y-m-d H:i:s'),
                'default' => false,
            ],
        ];
    }

    public function query(): Builder
    {
        if ($this->isTemplateMode()) {
            return Employee::query()->whereRaw('1 = 0');
        }

        $query = Employee::query()
            ->with(['school', 'department', 'employeeType'])
            ->latest();

        if (! empty($this->filters['search'])) {
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

        if (! empty($this->filters['employee_type']) && $this->filters['employee_type'] !== 'all') {
            $query->where('employee_type', $this->filters['employee_type']);
        }

        if (! empty($this->filters['school_id']) && $this->filters['school_id'] !== 'all') {
            $query->where('school_id', $this->filters['school_id']);
        }

        if (! empty($this->filters['department_id']) && $this->filters['department_id'] !== 'all') {
            $query->where('department_id', $this->filters['department_id']);
        }

        return $query;
    }

    public function headings(): array
    {
        return $this->selectedHeadings();
    }

    public function map($row): array
    {
        return $this->selectedRow($row);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
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
