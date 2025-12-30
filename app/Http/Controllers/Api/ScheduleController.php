<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends Controller
{
    private ScheduleService $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    /**
     * Check availability for a specific item and date range
     * Used by Flutter to check if item can be booked
     */
    public function checkAvailability(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'item_type' => 'required|in:costume,dance,makeup',
                'item_id' => 'required|integer',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after_or_equal:start_date',
                'quantity' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $result = $this->scheduleService->checkAvailability(
                $request->item_type,
                $request->item_id,
                $request->start_date,
                $request->end_date,
                $request->quantity
            );

            return response()->json([
                'success' => true,
                'data' => $result
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check availability: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get calendar events for a date range
     * Used by Flutter to display calendar view
     */
    public function getEvents(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'item_type' => 'nullable|in:costume,dance,makeup',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $events = $this->scheduleService->getCalendarEvents(
                $request->start_date,
                $request->end_date,
                $request->item_type
            );

            return response()->json([
                'success' => true,
                'data' => $events
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get events: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get month availability for a specific item
     * Shows available stock for each day in the month
     */
    public function getMonthAvailability(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'item_type' => 'required|in:costume,dance,makeup',
                'item_id' => 'required|integer',
                'year' => 'required|integer|min:2024',
                'month' => 'required|integer|min:1|max:12',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $availability = $this->scheduleService->getMonthAvailability(
                $request->item_type,
                $request->item_id,
                $request->year,
                $request->month
            );

            return response()->json([
                'success' => true,
                'data' => $availability
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get availability: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate order items availability before checkout
     */
    public function validateOrder(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'items' => 'required|array',
                'items.*.service_type' => 'required|in:kostum,tari,rias',
                'items.*.detail_id' => 'required|integer',
                'items.*.quantity' => 'required|integer|min:1',
                'start_date' => 'required|date|after_or_equal:today',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            $result = $this->scheduleService->validateOrder(
                $request->items,
                $request->start_date,
                $request->end_date
            );

            return response()->json([
                'success' => $result['valid'],
                'message' => $result['valid'] 
                    ? 'Semua item tersedia untuk tanggal yang dipilih' 
                    : 'Beberapa item tidak tersedia',
                'data' => $result
            ], $result['valid'] ? 200 : 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to validate order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get booked dates for a specific service
     * Returns list of dates that are fully booked (no stock available)
     * Used by Flutter calendar to disable booked dates
     */
    public function getBookedDates(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'service_type' => 'required|in:kostum,tari,rias',
                'detail_id' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get booked dates for the next 365 days
            $bookedDates = $this->scheduleService->getBookedDatesForService(
                $request->service_type,
                $request->detail_id,
                365
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'booked_dates' => $bookedDates,
                    'total_booked' => count($bookedDates),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get booked dates: ' . $e->getMessage()
            ], 500);
        }
    }
}
