<?php

namespace Modules\Employee\Actions\Dashboard\V1;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Employee\Models\Employee;

class UpdateEmployeeAction
{
    public function execute(Employee $employee, array $data): Employee
    {
        return DB::transaction(function () use ($employee, $data) {
            // Extract related data
            $familyMembers = $data['family_members'] ?? null;
            $academicLevels = $data['academic_levels'] ?? null;
            $foreignLanguages = $data['foreign_languages'] ?? null;
            $jobExperiences = $data['job_experiences'] ?? null;

            unset(
                $data['family_members'],
                $data['academic_levels'],
                $data['foreign_languages'],
                $data['job_experiences']
            );

            // Update employee
            $data['updated_by'] = Auth::id();
            $employee->update($data);

            // Sync family members if provided
            if ($familyMembers !== null) {
                $this->syncRelatedData($employee, 'familyMembers', $familyMembers, 'name');
            }

            // Sync academic levels if provided
            if ($academicLevels !== null) {
                $this->syncRelatedData($employee, 'academicLevels', $academicLevels, 'institution');
            }

            // Sync foreign languages if provided
            if ($foreignLanguages !== null) {
                $this->syncRelatedData($employee, 'foreignLanguages', $foreignLanguages, 'language');
            }

            // Sync job experiences if provided
            if ($jobExperiences !== null) {
                $this->syncRelatedData($employee, 'jobExperiences', $jobExperiences, 'company');
            }

            return $employee->fresh(['familyMembers', 'academicLevels', 'foreignLanguages', 'jobExperiences']);
        });
    }

    /**
     * Generic method to sync related data.
     * Creates new, updates existing, and soft deletes removed items.
     */
    protected function syncRelatedData(Employee $employee, string $relationship, array $items, string $requiredField): void
    {
        // Get IDs of items in the request
        $submittedIds = collect($items)
            ->filter(fn ($item) => isset($item['id']))
            ->pluck('id')
            ->toArray();

        // Soft delete items not in the request
        $employee->$relationship()
            ->whereNotIn('id', $submittedIds)
            ->delete();

        // Create or update items
        foreach ($items as $itemData) {
            // Remove Vue-specific keys
            unset($itemData['_key']);

            // Skip if required field is empty
            if (empty($itemData[$requiredField])) {
                continue;
            }

            // Clean empty strings to null
            $itemData = $this->cleanEmptyStrings($itemData);

            if (isset($itemData['id'])) {
                // Update existing item
                $item = $employee->$relationship()->find($itemData['id']);
                if ($item) {
                    unset($itemData['id']);
                    $item->update($itemData);
                }
            } else {
                // Create new item
                unset($itemData['id']);
                $employee->$relationship()->create($itemData);
            }
        }
    }

    /**
     * Convert empty strings to null for all fields.
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
