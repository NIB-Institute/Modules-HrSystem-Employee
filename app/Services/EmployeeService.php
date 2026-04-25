<?php

namespace Modules\Employee\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Modules\Employee\Models\Employee;

class EmployeeService
{
    /**
     * Get paginated employees with filters.
     */
    public function paginate(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        $query = Employee::with(['institution', 'department'])
            ->withCount('courses');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('employee_code', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['institution_id'])) {
            $query->where('institution_id', $filters['institution_id']);
        }

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (!empty($filters['employee_type']) && $filters['employee_type'] !== 'all') {
            $query->where('employee_type', $filters['employee_type']);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new employee.
     */
    public function create(array $data): Employee
    {
        $data['uuid'] = (string) Str::uuid();
        return Employee::create($data);
    }

    /**
     * Update an employee.
     */
    public function update(Employee $employee, array $data): Employee
    {
        $employee->update($data);
        return $employee->fresh();
    }

    /**
     * Delete an employee.
     */
    public function delete(Employee $employee): bool
    {
        return $employee->delete();
    }

    /**
     * Get employee statistics.
     */
    public function getStats(): array
    {
        return [
            'total' => Employee::count(),
            'active' => Employee::where('status', true)->count(),
            'inactive' => Employee::where('status', false)->count(),
            'full_time' => Employee::where('employee_type', 'full_time')->count(),
            'part_time' => Employee::where('employee_type', 'part_time')->count(),
            'contract' => Employee::where('employee_type', 'contract')->count(),
            'on_probation' => Employee::whereNotNull('probation_end_date')
                ->where('probation_end_date', '>', now())
                ->count(),
        ];
    }

    /**
     * Update employee status.
     */
    public function updateStatus(Employee $employee, bool $status): Employee
    {
        $employee->status = $status;
        $employee->save();
        return $employee;
    }

    /**
     * Find employee by UUID.
     */
    public function findByUuid(string $uuid): ?Employee
    {
        return Employee::where('uuid', $uuid)->first();
    }

    /**
     * Generate employee code.
     */
    public function generateEmployeeCode(): string
    {
        $lastEmployee = Employee::orderBy('id', 'desc')->first();
        $nextId = $lastEmployee ? $lastEmployee->id + 1 : 1;
        return 'EMP-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }
}
