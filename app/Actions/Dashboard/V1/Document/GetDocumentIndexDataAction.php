<?php

namespace Modules\Employee\Actions\Dashboard\V1\Document;

use Modules\Employee\Http\Resources\Dashboard\V1\DocumentResource;
use Modules\Employee\Models\Documents;

class GetDocumentIndexDataAction
{
    /**
     * @param  array{search?:string|null, extension?:string|null}  $filters
     */
    public function execute(int $perPage = 15, array $filters = []): array
    {
        $query = Documents::query()->with('uploader')->latest('id');

        if (! empty($filters['search'])) {
            $term = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('original_filename', 'like', $term)
                    ->orWhere('description', 'like', $term);
            });
        }

        if (! empty($filters['extension'])) {
            $query->where('extension', $filters['extension']);
        }

        $documents = $query->paginate($perPage)->withQueryString();

        $totals = [
            'total' => Documents::count(),
            'total_size_bytes' => (int) Documents::sum('size_bytes'),
        ];

        return [
            'documents' => [
                'data' => DocumentResource::collection($documents)->resolve(),
                'meta' => [
                    'current_page' => $documents->currentPage(),
                    'last_page'    => $documents->lastPage(),
                    'per_page'     => $documents->perPage(),
                    'total'        => $documents->total(),
                ],
            ],
            'filters' => [
                'search'    => $filters['search'] ?? '',
                'extension' => $filters['extension'] ?? '',
            ],
            'stats' => $totals,
        ];
    }
}
