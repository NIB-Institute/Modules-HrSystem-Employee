<?php

namespace Modules\Employee\Http\Controllers\Api\V1\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Employee\Actions\Api\V1\Attendance\CheckInAction;
use Modules\Employee\Actions\Api\V1\Attendance\CheckOutAction;
use Modules\Employee\Actions\Api\V1\Attendance\GetAttendanceHistoryAction;
use Modules\Employee\Actions\Api\V1\Attendance\GetTodayAttendanceAction;
use Modules\Employee\Http\Requests\Api\V1\Employee\ScanRequest;

class AttendanceController extends Controller
{
    /**
     * Get today's attendance status
     */
    public function today(Request $request, GetTodayAttendanceAction $action): JsonResponse
    {
        $result = $action->execute($request->user());

        return response()->json($result);
    }

    /**
     * Check-in (scan in)
     */
    public function checkIn(ScanRequest $request, CheckInAction $action): JsonResponse
    {
        $result = $action->execute($request->user(), $request->validated());

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Check-out (scan out)
     */
    public function checkOut(ScanRequest $request, CheckOutAction $action): JsonResponse
    {
        $result = $action->execute($request->user(), $request->validated());

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * Get attendance history
     */
    public function history(Request $request, GetAttendanceHistoryAction $action): JsonResponse
    {
        $result = $action->execute(
            $request->user(),
            $request->input('start_date'),
            $request->input('end_date'),
            $request->input('per_page', 15)
        );

        return response()->json($result);
    }
}
