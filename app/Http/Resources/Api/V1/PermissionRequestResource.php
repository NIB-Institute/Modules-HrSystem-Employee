<?php

namespace Modules\Employee\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Employee\Models\PermissionRequest;

class PermissionRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'uuid'  => $this->uuid,
            'type'  => $this->type,
            'type_label'        => $this->getTypeLabel(),
            'type_description'  => $this->getTypeDescription(),
            'reason'    => $this->reason,
            'from_date' => $this->from_date?->format('Y-m-d'),
            'to_date'   => $this->to_date?->format('Y-m-d'),
            'total_days'   => $this->getTotalDays(),
            'request_date' => $this->request_date?->toIso8601String(),
            'request_date_formatted' => $this->request_date?->format('d M Y H:i'),
            'status'       => $this->status,
            'status_label' => $this->getStatusLabel(),
            'is_pending'   => $this->isPending(),
            'is_approved' => $this->isApproved(),
            'is_rejected' => $this->isRejected(),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'reviewed_at_formatted' => $this->reviewed_at?->format('d M Y H:i'),
            'review_note'           => $this->review_note,
            'rejected_reason' => $this->rejected_reason,
            'reviewer' => $this->whenLoaded('reviewer', fn () => [
                'id'   => $this->reviewer->id,
                'name' => $this->reviewer->name,
            ]),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    /**
     * Get type label.
     */
    private function getTypeLabel(): string
    {
        return PermissionRequest::getTypes()[$this->type] ?? $this->type;
    }

    /**
     * Get type description.
     */
    private function getTypeDescription(): string
    {
        return PermissionRequest::getTypeDescriptions()[$this->type] ?? '';
    }

    /**
     * Get status label.
     */
    private function getStatusLabel(): string
    {
        return PermissionRequest::getStatuses()[$this->status] ?? $this->status;
    }
}
