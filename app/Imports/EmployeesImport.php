<?php

namespace Modules\Employee\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Modules\Employee\Models\Employee;
use Modules\Employee\Models\EmployeeType;
use Modules\School\Models\School;
use Modules\School\Models\Department;
use App\Services\TenantService;

class EmployeesImport implements ToCollection, WithHeadingRow, SkipsEmptyRows, WithBatchInserts, WithChunkReading
{
    public const DUPLICATE_SKIP = 'skip';
    public const DUPLICATE_UPDATE = 'update';
    public const DUPLICATE_FAIL = 'fail';

    protected array $errors = [];
    protected array $warnings = [];
    protected array $failedRows = [];
    protected int $importedCount = 0;
    protected int $updatedCount = 0;
    protected int $skippedCount = 0;
    protected ?int $defaultSchoolId;
    protected string $duplicateHandling;
    protected bool $previewMode = false;
    protected array $previewData = [];

    public function __construct(string $duplicateHandling = self::DUPLICATE_SKIP, bool $previewMode = false)
    {
        $tenantService = app(TenantService::class);
        $this->defaultSchoolId = $tenantService->hasTenantType('School')
            ? $tenantService->getTenantIds('School')[0] ?? null
            : null;
        $this->duplicateHandling = $duplicateHandling;
        $this->previewMode = $previewMode;
    }

    /**
     * Process the imported collection.
     */
    public function collection(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            $this->errors[] = [
                'row' => 0,
                'message' => 'No data rows found in the file.',
            ];
            return;
        }

        if ($this->previewMode) {
            $this->processPreview($rows);
            return;
        }

        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;
                $this->processRow($row->toArray(), $rowNumber);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errors[] = [
                'row' => 0,
                'message' => "Import failed: {$e->getMessage()}",
            ];
        }
    }

    /**
     * Process rows for preview mode.
     */
    protected function processPreview(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $data = $this->normalizeRow($row->toArray());

            // Resolve school name for preview
            $schoolName = $data['school'] ?? null;
            if ($schoolName) {
                $school = School::withoutGlobalScopes()->where('name', $schoolName)->first();
                if (!$school) {
                    $schoolName = $schoolName . ' (not found)';
                }
            } elseif ($this->defaultSchoolId) {
                $defaultSchool = School::withoutGlobalScopes()->find($this->defaultSchoolId);
                $schoolName = $defaultSchool ? $defaultSchool->name . ' (default)' : null;
            }

            // Resolve department name for preview
            $departmentName = $data['department'] ?? null;
            if ($departmentName) {
                $dept = Department::withoutGlobalScopes()->where('name', $departmentName)->first();
                if (!$dept) {
                    $departmentName = $departmentName . ' (not found)';
                }
            }

            $preview = [
                'row_number' => $rowNumber,
                'first_name' => $data['first_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'email' => $data['email'] ?? null,
                'phone_number' => $data['phone_number'] ?? null,
                'gender' => $this->formatGenderDisplay($data['gender'] ?? null),
                'school' => $schoolName,
                'department' => $departmentName,
                'job_title' => $data['job_title'] ?? null,
                'employee_type' => $this->formatEmployeeTypeDisplay($data['employment_type'] ?? $data['employee_type'] ?? null),
                'status' => 'ready',
                'errors' => [],
                'warnings' => [],
                'is_duplicate' => false,
                'existing_employee' => null,
            ];

            // Validate required fields
            $validator = Validator::make($data, [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
            ]);

            if ($validator->fails()) {
                $preview['status'] = 'error';
                $preview['errors'] = $validator->errors()->all();
            }

            // Validate school if specified
            if (!empty($data['school'])) {
                $school = School::withoutGlobalScopes()->where('name', $data['school'])->first();
                if (!$school) {
                    $preview['status'] = 'error';
                    $preview['errors'][] = "School '{$data['school']}' not found. Please check the school name matches exactly.";
                }
            }

            // Validate department if specified
            if (!empty($data['department'])) {
                $dept = Department::withoutGlobalScopes()->where('name', $data['department'])->first();
                if (!$dept) {
                    $preview['status'] = 'error';
                    $preview['errors'][] = "Department '{$data['department']}' not found. Please check the department name matches exactly.";
                }
            }

            // Check for duplicate
            if (!empty($data['email'])) {
                $existing = Employee::withoutGlobalScopes()->where('email', $data['email'])->first();
                if ($existing) {
                    $preview['is_duplicate'] = true;
                    $preview['existing_employee'] = [
                        'id' => $existing->id,
                        'name' => $existing->full_name,
                        'employee_code' => $existing->employee_code,
                    ];

                    if ($this->duplicateHandling === self::DUPLICATE_FAIL) {
                        $preview['status'] = 'error';
                        $preview['errors'][] = "Duplicate email: {$data['email']}";
                    } elseif ($this->duplicateHandling === self::DUPLICATE_SKIP) {
                        $preview['status'] = 'skip';
                        $preview['warnings'][] = 'Will be skipped (duplicate)';
                    } else {
                        $preview['status'] = 'update';
                        $preview['warnings'][] = 'Will update existing record';
                    }
                }
            }

            $this->previewData[] = $preview;
        }
    }

    /**
     * Process a single row for import.
     */
    protected function processRow(array $row, int $rowNumber): void
    {
        $data = $this->normalizeRow($row);

        // Validate required fields
        $validator = Validator::make($data, [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            $this->addFailedRow($rowNumber, $data, $validator->errors()->all());
            return;
        }

        // Validate school if specified
        if (!empty($data['school'])) {
            $school = School::withoutGlobalScopes()->where('name', $data['school'])->first();
            if (!$school) {
                $this->addFailedRow($rowNumber, $data, ["School '{$data['school']}' not found"]);
                return;
            }
        }

        // Validate department if specified
        if (!empty($data['department'])) {
            $dept = Department::withoutGlobalScopes()->where('name', $data['department'])->first();
            if (!$dept) {
                $this->addFailedRow($rowNumber, $data, ["Department '{$data['department']}' not found"]);
                return;
            }
        }

        // Handle duplicates
        $existingEmployee = null;
        if (!empty($data['email'])) {
            $existingEmployee = Employee::withoutGlobalScopes()->where('email', $data['email'])->first();

            if ($existingEmployee) {
                switch ($this->duplicateHandling) {
                    case self::DUPLICATE_FAIL:
                        $this->addFailedRow($rowNumber, $data, ["Email '{$data['email']}' already exists"]);
                        return;

                    case self::DUPLICATE_SKIP:
                        $this->skippedCount++;
                        $this->warnings[] = [
                            'row' => $rowNumber,
                            'message' => "Skipped: Email '{$data['email']}' already exists ({$existingEmployee->full_name})",
                        ];
                        return;

                    case self::DUPLICATE_UPDATE:
                        $this->updateEmployee($existingEmployee, $data, $rowNumber);
                        return;
                }
            }
        }

        // Create new employee
        $this->createEmployee($data, $rowNumber);
    }

    /**
     * Create a new employee.
     */
    protected function createEmployee(array $data, int $rowNumber): void
    {
        try {
            $schoolId = $this->resolveSchoolId($data);
            $departmentId = $this->resolveDepartmentId($data, $schoolId);
            $employeeTypeId = $this->resolveEmployeeTypeId($data);

            Employee::create([
                'uuid' => (string) Str::uuid(),
                'employee_code' => $this->generateEmployeeCode(),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'] ?? null,
                'phone_number' => $data['phone_number'] ?? null,
                'gender' => $this->normalizeGender($data['gender'] ?? null),
                'date_of_birth' => $this->parseDate($data['date_of_birth'] ?? null),
                'birth_place' => $data['birth_place'] ?? null,
                'current_address' => $data['current_address'] ?? null,
                'school_id' => $schoolId,
                'department_id' => $departmentId,
                'type_employee_id' => $employeeTypeId,
                'job_title' => $data['job_title'] ?? null,
                'employee_type' => $this->normalizeEmployeeType($data['employment_type'] ?? $data['employee_type'] ?? null),
                'salary' => $data['salary'] ?? null,
                'hire_date' => $this->parseDate($data['hire_date'] ?? null),
                'probation_date' => $this->parseDate($data['probation_date'] ?? null),
                'probation_end_date' => $this->parseDate($data['probation_end_date'] ?? null),
                'status' => $this->parseStatus($data['status'] ?? 'active'),
            ]);

            $this->importedCount++;
        } catch (\Exception $e) {
            $this->addFailedRow($rowNumber, $data, [$e->getMessage()]);
        }
    }

    /**
     * Update an existing employee.
     */
    protected function updateEmployee(Employee $employee, array $data, int $rowNumber): void
    {
        try {
            $schoolId = $this->resolveSchoolId($data) ?? $employee->school_id;
            $departmentId = $this->resolveDepartmentId($data, $schoolId) ?? $employee->department_id;
            $employeeTypeId = $this->resolveEmployeeTypeId($data) ?? $employee->type_employee_id;

            $employee->update([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone_number' => $data['phone_number'] ?? $employee->phone_number,
                'gender' => $this->normalizeGender($data['gender'] ?? null) ?? $employee->gender,
                'date_of_birth' => $this->parseDate($data['date_of_birth'] ?? null) ?? $employee->date_of_birth,
                'birth_place' => $data['birth_place'] ?? $employee->birth_place,
                'current_address' => $data['current_address'] ?? $employee->current_address,
                'school_id' => $schoolId,
                'department_id' => $departmentId,
                'type_employee_id' => $employeeTypeId,
                'job_title' => $data['job_title'] ?? $employee->job_title,
                'employee_type' => $this->normalizeEmployeeType($data['employment_type'] ?? $data['employee_type'] ?? null) ?? $employee->employee_type,
                'salary' => $data['salary'] ?? $employee->salary,
                'hire_date' => $this->parseDate($data['hire_date'] ?? null) ?? $employee->hire_date,
                'status' => $this->parseStatus($data['status'] ?? 'active'),
            ]);

            $this->updatedCount++;
        } catch (\Exception $e) {
            $this->addFailedRow($rowNumber, $data, [$e->getMessage()]);
        }
    }

    /**
     * Add a failed row to the report.
     */
    protected function addFailedRow(int $rowNumber, array $data, array $errors): void
    {
        $this->failedRows[] = [
            'row_number' => $rowNumber,
            'data' => $data,
            'errors' => $errors,
        ];
        $this->skippedCount++;
    }

    /**
     * Resolve school ID from data.
     */
    protected function resolveSchoolId(array $data): ?int
    {
        // First, check if a school name is provided in the Excel data
        if (!empty($data['school'])) {
            $school = School::withoutGlobalScopes()
                ->where('name', $data['school'])
                ->first();

            if ($school) {
                return $school->id;
            }
        }

        // Fall back to default school if no school specified or not found
        if ($this->defaultSchoolId) {
            return $this->defaultSchoolId;
        }

        // For super-admin, get first school
        $tenantService = app(TenantService::class);
        if (!$tenantService->hasTenant()) {
            $firstSchool = School::withoutGlobalScopes()->first();
            return $firstSchool?->id;
        }

        return null;
    }

    /**
     * Resolve department ID from data.
     */
    protected function resolveDepartmentId(array $data, ?int $schoolId): ?int
    {
        if (!empty($data['department'])) {
            // First try to find department by name within the school
            if ($schoolId) {
                $department = Department::withoutGlobalScopes()
                    ->where('school_id', $schoolId)
                    ->where('name', $data['department'])
                    ->first();
                if ($department) {
                    return $department->id;
                }
            }

            // If not found in school, try global search by name
            $department = Department::withoutGlobalScopes()
                ->where('name', $data['department'])
                ->first();
            return $department?->id;
        }
        return null;
    }

    /**
     * Resolve employee type ID from data.
     */
    protected function resolveEmployeeTypeId(array $data): ?int
    {
        if (!empty($data['employee_type_name'])) {
            $employeeType = EmployeeType::withoutGlobalScopes()
                ->where('name', $data['employee_type_name'])
                ->first();
            return $employeeType?->id;
        }
        return null;
    }

    /**
     * Normalize row keys to snake_case.
     */
    protected function normalizeRow(array $row): array
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            $normalizedKey = Str::snake(str_replace(' ', '_', strtolower(trim($key))));
            $normalized[$normalizedKey] = is_string($value) ? trim($value) : $value;
        }
        return $normalized;
    }

    /**
     * Generate unique employee code.
     */
    protected function generateEmployeeCode(): string
    {
        $lastEmployee = Employee::withoutGlobalScopes()->orderBy('id', 'desc')->first();
        $lastNumber = 0;

        if ($lastEmployee && preg_match('/EMP-(\d+)/', $lastEmployee->employee_code, $matches)) {
            $lastNumber = (int) $matches[1];
        }

        return 'EMP-' . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Parse date value.
     */
    protected function parseDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if (is_numeric($value)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
        }

        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Parse status value.
     */
    protected function parseStatus($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $value = strtolower(trim((string) $value));
        return in_array($value, ['1', 'true', 'yes', 'active', 'enabled']);
    }

    /**
     * Normalize gender value.
     */
    protected function normalizeGender(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $value = strtolower(trim($value));

        return match ($value) {
            'male', 'm' => 'male',
            'female', 'f' => 'female',
            'other', 'o' => 'other',
            default => null,
        };
    }

    /**
     * Normalize employee type value.
     */
    protected function normalizeEmployeeType(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $value = strtolower(str_replace(' ', '_', trim($value)));

        return match ($value) {
            'full_time', 'fulltime', 'full-time' => 'full_time',
            'part_time', 'parttime', 'part-time' => 'part_time',
            'contract' => 'contract',
            'intern', 'internship' => 'intern',
            default => null,
        };
    }

    /**
     * Format gender for display.
     */
    protected function formatGenderDisplay(?string $value): ?string
    {
        $normalized = $this->normalizeGender($value);
        return $normalized ? ucfirst($normalized) : $value;
    }

    /**
     * Format employee type for display.
     */
    protected function formatEmployeeTypeDisplay(?string $value): ?string
    {
        $normalized = $this->normalizeEmployeeType($value);

        return match ($normalized) {
            'full_time' => 'Full Time',
            'part_time' => 'Part Time',
            'contract' => 'Contract',
            'intern' => 'Intern',
            default => $value,
        };
    }

    /**
     * Get preview data.
     */
    public function getPreviewData(): array
    {
        return $this->previewData;
    }

    /**
     * Get import results.
     */
    public function getResults(): array
    {
        $previewStats = $this->getPreviewStats();

        return [
            'imported' => $this->importedCount,
            'updated' => $this->updatedCount,
            'skipped' => $this->skippedCount,
            'failed' => count($this->failedRows),
            'total_rows' => $this->importedCount + $this->updatedCount + $this->skippedCount + count($this->failedRows),
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'failed_rows' => $this->failedRows,
            'preview_stats' => $previewStats,
        ];
    }

    /**
     * Get preview statistics.
     */
    protected function getPreviewStats(): array
    {
        if (empty($this->previewData)) {
            return [];
        }

        $stats = [
            'total' => count($this->previewData),
            'ready' => 0,
            'update' => 0,
            'skip' => 0,
            'error' => 0,
        ];

        foreach ($this->previewData as $row) {
            $status = $row['status'] ?? 'ready';
            if (isset($stats[$status])) {
                $stats[$status]++;
            }
        }

        return $stats;
    }

    /**
     * Get failed rows for download.
     */
    public function getFailedRows(): array
    {
        return $this->failedRows;
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
