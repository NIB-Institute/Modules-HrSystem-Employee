<?php

namespace Modules\Employee\Http\Resources\Dashboard\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImportPreviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'row_number' => $this->resource['row_number'],
            'first_name' => $this->resource['first_name'] ?? null,
            'last_name' => $this->resource['last_name'] ?? null,
            'email' => $this->resource['email'] ?? null,
            'phone_number' => $this->resource['phone_number'] ?? null,
            'gender' => $this->resource['gender'] ?? null,
            'school' => $this->resource['school'] ?? null,
            'department' => $this->resource['department'] ?? null,
            'job_title' => $this->resource['job_title'] ?? null,
            'employee_type' => $this->resource['employee_type'] ?? null,
            'status' => $this->resource['status'] ?? 'pending',
            'errors' => $this->resource['errors'] ?? [],
            'warnings' => $this->resource['warnings'] ?? [],
            'is_duplicate' => $this->resource['is_duplicate'] ?? false,
            'existing_employee' => $this->resource['existing_employee'] ?? null,
        ];
    }
}
