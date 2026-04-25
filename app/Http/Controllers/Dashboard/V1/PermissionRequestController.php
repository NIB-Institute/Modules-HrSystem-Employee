<?php

namespace Modules\Employee\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Momentum\Modal\Modal;
use Modules\Employee\Actions\Dashboard\V1\PermissionRequest\CreatePermissionRequestAction;
use Modules\Employee\Actions\Dashboard\V1\PermissionRequest\DeletePermissionRequestAction;
use Modules\Employee\Actions\Dashboard\V1\PermissionRequest\GetPermissionRequestCreateDataAction;
use Modules\Employee\Actions\Dashboard\V1\PermissionRequest\GetPermissionRequestEditDataAction;
use Modules\Employee\Actions\Dashboard\V1\PermissionRequest\GetPermissionRequestIndexDataAction;
use Modules\Employee\Actions\Dashboard\V1\PermissionRequest\ReviewPermissionRequestAction;
use Modules\Employee\Actions\Dashboard\V1\PermissionRequest\UpdatePermissionRequestAction;
use Modules\Employee\Http\Requests\Dashboard\V1\ReviewPermissionRequestRequest;
use Modules\Employee\Http\Requests\Dashboard\V1\StorePermissionRequestRequest;
use Modules\Employee\Http\Requests\Dashboard\V1\UpdatePermissionRequestRequest;
use Modules\Employee\Http\Resources\Dashboard\V1\PermissionRequestResource;
use Modules\Employee\Models\PermissionRequest;

class PermissionRequestController extends Controller
{
    public function __construct(
        protected GetPermissionRequestIndexDataAction $getIndexDataAction,
        protected GetPermissionRequestCreateDataAction $getCreateDataAction,
        protected GetPermissionRequestEditDataAction $getEditDataAction,
        protected CreatePermissionRequestAction $createAction,
        protected UpdatePermissionRequestAction $updateAction,
        protected DeletePermissionRequestAction $deleteAction,
        protected ReviewPermissionRequestAction $reviewAction,
    ) {}

    /**
     * Display a listing of permission requests.
     */
    public function index(Request $request): Response
    {
        $perPage = $request->input('per_page', 10);
        $filters = $request->only(['search', 'employee_id', 'type', 'status', 'date_from', 'date_to']);

        $data = $this->getIndexDataAction->execute($perPage, $filters);

        return Inertia::render('employee::Dashboard/V1/PermissionRequest/Index', $data);
    }

    /**
     * Show the form for creating a new request.
     */
    public function create(Request $request): Modal
    {
        $employeeId = $request->input('employee_id');
        $data = $this->getCreateDataAction->execute($employeeId);

        return Inertia::modal('employee::Dashboard/V1/PermissionRequest/Create', $data)
            ->baseRoute('employee.permission-requests.index');
    }

    /**
     * Store a newly created request.
     */
    public function store(StorePermissionRequestRequest $request): RedirectResponse
    {
        $this->createAction->execute($request->validated());

        return redirect()
            ->route('employee.permission-requests.index')
            ->with('success', 'Permission request submitted successfully.');
    }

    /**
     * Display the specified request.
     */
    public function show(PermissionRequest $permissionRequest): Response
    {
        $permissionRequest->load(['employee', 'reviewer']);

        return Inertia::render('employee::Dashboard/V1/PermissionRequest/Show', [
            'permissionRequest' => (new PermissionRequestResource($permissionRequest))->resolve(),
            'types' => PermissionRequest::getTypes(),
            'statuses' => PermissionRequest::getStatuses(),
        ]);
    }

    /**
     * Show the form for editing the request.
     */
    public function edit(PermissionRequest $permissionRequest): Modal
    {
        // Only allow editing pending requests
        if (!$permissionRequest->isPending()) {
            return redirect()
                ->route('employee.permission-requests.index')
                ->with('error', 'Only pending requests can be edited.');
        }

        $data = $this->getEditDataAction->execute($permissionRequest);

        return Inertia::modal('employee::Dashboard/V1/PermissionRequest/Edit', $data)
            ->baseRoute('employee.permission-requests.index');
    }

    /**
     * Update the specified request.
     */
    public function update(UpdatePermissionRequestRequest $request, PermissionRequest $permissionRequest): RedirectResponse
    {
        // Only allow updating pending requests
        if (!$permissionRequest->isPending()) {
            return redirect()
                ->route('employee.permission-requests.index')
                ->with('error', 'Only pending requests can be updated.');
        }

        $this->updateAction->execute($permissionRequest, $request->validated());

        return redirect()
            ->route('employee.permission-requests.index')
            ->with('success', 'Permission request updated successfully.');
    }

    /**
     * Show delete confirmation modal.
     */
    public function confirmDelete(PermissionRequest $permissionRequest): Modal
    {
        $permissionRequest->load('employee');

        return Inertia::modal('employee::Dashboard/V1/PermissionRequest/Delete', [
            'permissionRequest' => (new PermissionRequestResource($permissionRequest))->resolve(),
        ])->baseRoute('employee.permission-requests.index');
    }

    /**
     * Remove the specified request.
     */
    public function destroy(PermissionRequest $permissionRequest): RedirectResponse
    {
        $this->deleteAction->execute($permissionRequest);

        return redirect()
            ->route('employee.permission-requests.index')
            ->with('success', 'Permission request deleted successfully.');
    }

    /**
     * Show review modal.
     */
    public function showReview(PermissionRequest $permissionRequest): Modal
    {
        $permissionRequest->load('employee');

        return Inertia::modal('employee::Dashboard/V1/PermissionRequest/Review', [
            'permissionRequest' => (new PermissionRequestResource($permissionRequest))->resolve(),
        ])->baseRoute('employee.permission-requests.index');
    }

    /**
     * Review (approve/reject) the request.
     */
    public function review(ReviewPermissionRequestRequest $request, PermissionRequest $permissionRequest): RedirectResponse
    {
        if (!$permissionRequest->isPending()) {
            return redirect()
                ->route('employee.permission-requests.index')
                ->with('error', 'This request has already been reviewed.');
        }

        $validated = $request->validated();
        $this->reviewAction->execute(
            $permissionRequest,
            $validated['action'],
            $validated['review_note'] ?? null,
            $validated['rejected_status'] ?? false,
            $validated['rejected_reason'] ?? null
        );

        $action = $validated['action'] === 'approve' ? 'approved' : 'rejected';

        return redirect()
            ->route('employee.permission-requests.index')
            ->with('success', "Permission request {$action} successfully.");
    }
}
