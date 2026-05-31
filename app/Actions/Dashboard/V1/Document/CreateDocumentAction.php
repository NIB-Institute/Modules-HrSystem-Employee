<?php

namespace Modules\Employee\Actions\Dashboard\V1\Document;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Modules\Employee\Models\Documents;

class CreateDocumentAction
{
    private const STORAGE_PATH = 'employee/documents';

    public function execute(string $name, UploadedFile $file, ?string $description = null): Documents
    {
        $storedPath = $file->store(self::STORAGE_PATH, 'public');

        return Documents::create([
            'name'        => $name,
            'description' => $description,
            'original_filename' => $file->getClientOriginalName(),
            'file_path'     => $storedPath,
            'mime_type'     => $file->getClientMimeType() ?: 'application/octet-stream',
            'size_bytes'    => $file->getSize() ?: 0,
            'extension'     => strtolower($file->getClientOriginalExtension()) ?: null,
            'uploaded_by'   => Auth::id(),
        ]);
    }
}
