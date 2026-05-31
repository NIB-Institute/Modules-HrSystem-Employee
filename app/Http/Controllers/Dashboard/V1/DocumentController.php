<?php

namespace Modules\Employee\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Momentum\Modal\Modal;
use Modules\Employee\Actions\Dashboard\V1\Document\CreateDocumentAction;
use Modules\Employee\Actions\Dashboard\V1\Document\DeleteDocumentAction;
use Modules\Employee\Actions\Dashboard\V1\Document\GetDocumentIndexDataAction;
use Modules\Employee\Actions\Dashboard\V1\Document\UpdateDocumentAction;
use Modules\Employee\Http\Requests\Dashboard\V1\StoreDocumentRequest;
use Modules\Employee\Http\Requests\Dashboard\V1\UpdateDocumentRequest;
use Modules\Employee\Http\Resources\Dashboard\V1\DocumentResource;
use Modules\Employee\Models\Documents;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentController extends Controller
{
    public function __construct(
        protected GetDocumentIndexDataAction $getIndexDataAction,
        protected CreateDocumentAction $createAction,
        protected UpdateDocumentAction $updateAction,
        protected DeleteDocumentAction $deleteAction,
    ) {}

    public function index(Request $request): Response
    {
        $perPage = (int) $request->input('per_page', 15);
        $filters = $request->only(['search', 'extension']);

        return Inertia::render(
            'employee::Dashboard/V1/Document/Index',
            $this->getIndexDataAction->execute($perPage, $filters),
        );
    }

    public function create(): Modal
    {
        return Inertia::modal('employee::Dashboard/V1/Document/Create', [])
            ->baseRoute('employee.documents.index');
    }

    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        $this->createAction->execute(
            $request->validated()['name'],
            $request->file('file'),
            $request->validated()['description'] ?? null,
        );

        return redirect()
            ->route('employee.documents.index')
            ->with('success', 'Document uploaded successfully.');
    }

    public function edit(Documents $document): Modal
    {
        return Inertia::modal('employee::Dashboard/V1/Document/Edit', [
            'document' => (new DocumentResource($document))->resolve(),
        ])->baseRoute('employee.documents.index');
    }

    public function update(UpdateDocumentRequest $request, Documents $document): RedirectResponse
    {
        $this->updateAction->execute(
            $document,
            $request->validated()['name'],
            $request->validated()['description'] ?? null,
            $request->file('file'),
        );

        return redirect()
            ->route('employee.documents.index')
            ->with('success', 'Document updated successfully.');
    }

    public function confirmDelete(Documents $document): Modal
    {
        return Inertia::modal('employee::Dashboard/V1/Document/Delete', [
            'document' => (new DocumentResource($document))->resolve(),
        ])->baseRoute('employee.documents.index');
    }

    public function destroy(Documents $document): RedirectResponse
    {
        $this->deleteAction->execute($document);

        return redirect()
            ->route('employee.documents.index')
            ->with('success', 'Document deleted successfully.');
    }

    /**
     * Force-download the file with its original filename. Streams via
     * BinaryFileResponse so large files don't load into PHP memory.
     */
    public function download(Documents $document): BinaryFileResponse
    {
        abort_unless(
            $document->file_path && Storage::disk('public')->exists($document->file_path),
            404,
            'File no longer exists on disk.',
        );

        $absolutePath = Storage::disk('public')->path($document->file_path);

        return response()->download(
            $absolutePath,
            $document->original_filename ?: $document->name,
            ['Content-Type' => $document->mime_type ?: 'application/octet-stream'],
        );
    }
}
