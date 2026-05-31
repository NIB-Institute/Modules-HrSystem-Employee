<?php

namespace Modules\Employee\Actions\Dashboard\V1\Document;

use Illuminate\Support\Facades\Storage;
use Modules\Employee\Models\Documents;

class DeleteDocumentAction
{
    /**
     * Soft-delete the model AND immediately remove the file from disk.
     * (We don't keep the underlying file for soft-deleted documents to avoid
     * orphaned storage. If you want recoverable deletes, drop the Storage
     * delete here and only purge on forceDelete.)
     */
    public function execute(Documents $document): void
    {
        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();
    }
}
