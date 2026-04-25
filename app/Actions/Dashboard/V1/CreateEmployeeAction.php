<?php

namespace Modules\Employee\Actions\Dashboard\V1;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Employee\Models\Employee;
use Modules\Employee\Models\EmployeeFamilyMember;

class CreateEmployeeAction
{
    public function execute(array $data): Employee
    {
        return DB::transaction(function () use ($data) {
            // Extract account creation fields
            $createAccount = $data['create_account'] ?? false;
            $password = $data['password'] ?? null;

            // Extract related data
            $familyMembers = $data['family_members'] ?? [];
            $academicLevels = $data['academic_levels'] ?? [];
            $foreignLanguages = $data['foreign_languages'] ?? [];
            $jobExperiences = $data['job_experiences'] ?? [];

            // Remove non-employee fields
            unset(
                $data['create_account'],
                $data['password'],
                $data['password_confirmation'],
                $data['family_members'],
                $data['academic_levels'],
                $data['foreign_languages'],
                $data['job_experiences']
            );

            // Create User account if requested
            $userId = null;
            if ($createAccount && !empty($data['email']) && $password) {
                $user = User::create([
                    'name' => trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')),
                    'email' => $data['email'],
                    'password' => Hash::make($password),
                    'email_verified_at' => now(),
                ]);

                // Assign employee role if exists
                if (method_exists($user, 'assignRole')) {
                    try {
                        $user->assignRole('employee');
                    } catch (\Exception $e) {
                        // Role might not exist, continue without it
                    }
                }

                $userId = $user->id;
            }

            // Prepare employee data
            $data['uuid'] = (string) Str::uuid();
            $data['created_by'] = Auth::id();
            $data['user_id'] = $userId;

            $employee = Employee::create($data);

            // Create family members if provided
            if (!empty($familyMembers)) {
                $this->createFamilyMembers($employee, $familyMembers);
            }

            // Create academic levels if provided
            if (!empty($academicLevels)) {
                $this->createAcademicLevels($employee, $academicLevels);
            }

            // Create foreign languages if provided
            if (!empty($foreignLanguages)) {
                $this->createForeignLanguages($employee, $foreignLanguages);
            }

            // Create job experiences if provided
            if (!empty($jobExperiences)) {
                $this->createJobExperiences($employee, $jobExperiences);
            }

            return $employee;
        });
    }

    /**
     * Create family members for the employee.
     */
    protected function createFamilyMembers(Employee $employee, array $familyMembers): void
    {
        foreach ($familyMembers as $memberData) {
            // Remove Vue-specific keys
            unset($memberData['_key']);

            // Skip if name is empty
            if (empty($memberData['name'])) {
                continue;
            }

            // Clean empty strings to null
            $memberData = $this->cleanEmptyStrings($memberData);

            $employee->familyMembers()->create($memberData);
        }
    }

    /**
     * Create academic levels for the employee.
     */
    protected function createAcademicLevels(Employee $employee, array $academicLevels): void
    {
        foreach ($academicLevels as $levelData) {
            unset($levelData['_key']);

            if (empty($levelData['institution'])) {
                continue;
            }

            $levelData = $this->cleanEmptyStrings($levelData);
            $employee->academicLevels()->create($levelData);
        }
    }

    /**
     * Create foreign languages for the employee.
     */
    protected function createForeignLanguages(Employee $employee, array $foreignLanguages): void
    {
        foreach ($foreignLanguages as $langData) {
            unset($langData['_key']);

            if (empty($langData['language'])) {
                continue;
            }

            $langData = $this->cleanEmptyStrings($langData);
            $employee->foreignLanguages()->create($langData);
        }
    }

    /**
     * Create job experiences for the employee.
     */
    protected function createJobExperiences(Employee $employee, array $jobExperiences): void
    {
        foreach ($jobExperiences as $expData) {
            unset($expData['_key']);

            if (empty($expData['company'])) {
                continue;
            }

            $expData = $this->cleanEmptyStrings($expData);
            $employee->jobExperiences()->create($expData);
        }
    }

    /**
     * Convert empty strings to null for optional fields.
     */
    protected function cleanEmptyStrings(array $data): array
    {
        foreach ($data as $key => $value) {
            if ($value === '') {
                $data[$key] = null;
            }
        }

        return $data;
    }
}
