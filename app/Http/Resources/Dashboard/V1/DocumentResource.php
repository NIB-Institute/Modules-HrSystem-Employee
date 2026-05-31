<?php

namespace Modules\Employee\Http\Resources\Dashboard\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
            'uuid'  => $this->uuid,
            'name'  => $this->name,
            'original_filename' => $this->original_filename,
            'mime_type'     => $this->mime_type,
            'extension'     => $this->extension,
            'size_bytes'    => (int) $this->size_bytes,
            'human_size'    => $this->human_size,
            'description'   => $this->description,
            'url' => $this->url,
            'uploaded_by'   => $this->uploaded_by,
            'uploader_name' => $this->whenLoaded('uploader', fn () => $this->uploader?->name),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
