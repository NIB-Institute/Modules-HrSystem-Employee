<?php

namespace Modules\Employee\Http\Controllers\Dashboard\V1;

use App\Http\Controllers\Controller;
use App\Services\TenantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Employee\Models\Employee;
use Modules\Employee\Models\Location;
use Modules\School\Models\School;

class LocationController extends Controller
{
    /**
     * Display a listing of locations.
     */
    public function index(Request $request): Response
    {
        $query = Location::with('school:id,name');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('code', 'like', '%' . $request->search . '%')
                    ->orWhere('city', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $locations = $query->orderBy('name')->paginate(10)->withQueryString();

        return Inertia::render('employee::Dashboard/V1/Location/Index', [
            'locationsList' => $locations,
            'filters' => [
                'search' => $request->search,
                'type' => $request->type,
                'status' => $request->status,
            ],
            'types' => Location::getTypes(),
        ]);
    }

    /**
     * Show the form for creating a new location.
     */
    public function create(): Response
    {
        $tenantService = app(TenantService::class);

        // Get schools based on user's tenant access
        $schools = School::query()
            ->select('id', 'name')
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('employee::Dashboard/V1/Location/Create', [
            'types' => Location::getTypes(),
            'geofenceTypes' => Location::getGeofenceTypes(),
            'employees' => Employee::select('id', 'first_name', 'last_name')
                ->where('status', true)
                ->orderBy('first_name')
                ->get()
                ->map(fn ($e) => [
                    'id' => $e->id,
                    'full_name' => $e->first_name . ' ' . $e->last_name,
                ]),
            'schools' => $schools,
            'defaultSchoolId' => $tenantService->hasTenantType('School')
                ? $tenantService->getTenantId()
                : $schools->first()?->id,
        ]);
    }

    /**
     * Store a newly created location.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:employee_locations,code',
            'description' => 'nullable|string',
            'type' => 'required|in:office,branch,site,remote,other',
            'address_line_1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'geofence_radius' => 'required|integer|min:10|max:10000',
            'geofence_type' => 'required|in:circle,polygon,dynamic',
            'polygon_coordinates' => 'nullable|array|min:3',
            'polygon_coordinates.*' => 'array:0,1',
            'polygon_coordinates.*.0' => 'numeric|between:-90,90',
            'polygon_coordinates.*.1' => 'numeric|between:-180,180',
            'reference_employee_id' => 'nullable|exists:employees,id',
            'dynamic_radius' => 'nullable|integer|min:10|max:1000',
            'enforce_geofence' => 'boolean',
            'timezone' => 'required|string|max:50',
            'operating_hours' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $validated['enforce_geofence'] = $validated['enforce_geofence'] ?? true;
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['geofence_type'] = $validated['geofence_type'] ?? 'circle';

        Location::create($validated);

        return redirect()
            ->route('employee.locations.index')
            ->with('success', 'Location created successfully.');
    }

    /**
     * Display the specified location.
     */
    public function show(Location $location): Response
    {
        return Inertia::render('employee::Dashboard/V1/Location/Show', [
            'locationData' => $location,
            'types' => Location::getTypes(),
        ]);
    }

    /**
     * Show the form for editing the specified location.
     */
    public function edit(Location $location): Response
    {
        $schools = School::query()
            ->select('id', 'name')
            ->where('status', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('employee::Dashboard/V1/Location/Edit', [
            'locationData' => $location,
            'types' => Location::getTypes(),
            'geofenceTypes' => Location::getGeofenceTypes(),
            'employees' => Employee::select('id', 'first_name', 'last_name')
                ->where('status', true)
                ->orderBy('first_name')
                ->get()
                ->map(fn ($e) => [
                    'id' => $e->id,
                    'full_name' => $e->first_name . ' ' . $e->last_name,
                ]),
            'schools' => $schools,
        ]);
    }

    /**
     * Update the specified location.
     */
    public function update(Request $request, Location $location): RedirectResponse
    {
        $validated = $request->validate([
            'school_id' => 'required|exists:schools,id',
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:employee_locations,code,' . $location->id,
            'description' => 'nullable|string',
            'type' => 'required|in:office,branch,site,remote,other',
            'address_line_1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'geofence_radius' => 'required|integer|min:10|max:10000',
            'geofence_type' => 'required|in:circle,polygon,dynamic',
            'polygon_coordinates' => 'nullable|array|min:3',
            'polygon_coordinates.*' => 'array:0,1',
            'polygon_coordinates.*.0' => 'numeric|between:-90,90',
            'polygon_coordinates.*.1' => 'numeric|between:-180,180',
            'reference_employee_id' => 'nullable|exists:employees,id',
            'dynamic_radius' => 'nullable|integer|min:10|max:1000',
            'enforce_geofence' => 'boolean',
            'timezone' => 'required|string|max:50',
            'operating_hours' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $location->update($validated);

        return redirect()
            ->route('employee.locations.index')
            ->with('success', 'Location updated successfully.');
    }

    /**
     * Update the schedule/operating hours for a location.
     */
    public function updateSchedule(Request $request, Location $location): RedirectResponse
    {
        $validated = $request->validate([
            'operating_hours' => 'nullable|array',
            'operating_hours.monday' => 'nullable|array',
            'operating_hours.monday.start' => 'required_with:operating_hours.monday|date_format:H:i',
            'operating_hours.monday.end' => 'required_with:operating_hours.monday|date_format:H:i|after:operating_hours.monday.start',
            'operating_hours.tuesday' => 'nullable|array',
            'operating_hours.tuesday.start' => 'required_with:operating_hours.tuesday|date_format:H:i',
            'operating_hours.tuesday.end' => 'required_with:operating_hours.tuesday|date_format:H:i|after:operating_hours.tuesday.start',
            'operating_hours.wednesday' => 'nullable|array',
            'operating_hours.wednesday.start' => 'required_with:operating_hours.wednesday|date_format:H:i',
            'operating_hours.wednesday.end' => 'required_with:operating_hours.wednesday|date_format:H:i|after:operating_hours.wednesday.start',
            'operating_hours.thursday' => 'nullable|array',
            'operating_hours.thursday.start' => 'required_with:operating_hours.thursday|date_format:H:i',
            'operating_hours.thursday.end' => 'required_with:operating_hours.thursday|date_format:H:i|after:operating_hours.thursday.start',
            'operating_hours.friday' => 'nullable|array',
            'operating_hours.friday.start' => 'required_with:operating_hours.friday|date_format:H:i',
            'operating_hours.friday.end' => 'required_with:operating_hours.friday|date_format:H:i|after:operating_hours.friday.start',
            'operating_hours.saturday' => 'nullable|array',
            'operating_hours.saturday.start' => 'required_with:operating_hours.saturday|date_format:H:i',
            'operating_hours.saturday.end' => 'required_with:operating_hours.saturday|date_format:H:i|after:operating_hours.saturday.start',
            'operating_hours.sunday' => 'nullable|array',
            'operating_hours.sunday.start' => 'required_with:operating_hours.sunday|date_format:H:i',
            'operating_hours.sunday.end' => 'required_with:operating_hours.sunday|date_format:H:i|after:operating_hours.sunday.start',
        ]);

        $location->update([
            'operating_hours' => $validated['operating_hours'],
        ]);

        return redirect()
            ->back()
            ->with('success', 'Schedule updated successfully.');
    }

    /**
     * Toggle the active status of a location.
     */
    public function toggleStatus(Request $request, Location $location): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|boolean',
        ]);

        $location->update([
            'is_active' => $validated['status'],
        ]);

        $status = $validated['status'] ? 'activated' : 'deactivated';

        return redirect()
            ->back()
            ->with('success', "Location {$status} successfully.");
    }

    /**
     * Remove the specified location.
     */
    public function destroy(Location $location): RedirectResponse
    {
        $location->delete();

        return redirect()
            ->route('employee.locations.index')
            ->with('success', 'Location deleted successfully.');
    }
}
