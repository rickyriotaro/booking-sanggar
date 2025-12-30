<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ScheduleService;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    private ScheduleService $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    /**
     * Display calendar view
     */
    public function index(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $itemType = $request->get('item_type');

        $events = $this->scheduleService->getCalendarEvents($startDate, $endDate, $itemType);
        
        // Get all dates with orders for current month only
        $today = now();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();
        
        $datesWithOrders = $this->scheduleService->getDatesWithOrders(
            $monthStart->format('Y-m-d'),
            $monthEnd->format('Y-m-d')
        );

        return view('admin.schedule.index', compact('events', 'datesWithOrders'));
    }

    /**
     * Get booked dates summary
     */
    public function getBookedDates(Request $request)
    {
        $days = $request->get('days', 30); // Default 30 days
        
        $costumeBookedDates = $this->scheduleService->getBookedDatesForService('costume', null, $days);
        $danceBookedDates = $this->scheduleService->getBookedDatesForService('dance', null, $days);
        $makeupBookedDates = $this->scheduleService->getBookedDatesForService('makeup', null, $days);

        return response()->json([
            'costume' => $costumeBookedDates,
            'dance' => $danceBookedDates,
            'makeup' => $makeupBookedDates,
        ]);
    }

    /**
     * Get events for AJAX calendar
     */
    public function getEvents(Request $request)
    {
        $events = $this->scheduleService->getCalendarEvents(
            $request->start,
            $request->end,
            $request->item_type
        );

        // Format for FullCalendar
        $calendarEvents = [];
        foreach ($events as $event) {
            $colorMap = [
                'pending' => '#fbbf24', // yellow
                'confirmed' => '#3b82f6', // blue
                'processing' => '#8b5cf6', // purple
                'ready' => '#10b981', // green
                'completed' => '#6b7280', // gray
            ];

            $calendarEvents[] = [
                'id' => $event['id'],
                'title' => $event['order_code'] . ' - ' . $event['customer_name'],
                'start' => $event['start_date'],
                'end' => $event['end_date'],
                'backgroundColor' => $colorMap[$event['status']] ?? '#6b7280',
                'borderColor' => $colorMap[$event['status']] ?? '#6b7280',
                'extendedProps' => [
                    'order_code' => $event['order_code'],
                    'customer' => $event['customer_name'],
                    'status' => $event['status'],
                    'total' => $event['total_amount'],
                    'items' => $event['items'],
                ],
            ];
        }

        return response()->json($calendarEvents);
    }

    /**
     * Get all orders for a specific date
     */
    public function getOrdersByDate(Request $request)
    {
        $date = $request->get('date');
        
        if (!$date) {
            return response()->json([
                'success' => false,
                'message' => 'Date is required'
            ], 400);
        }

        $orders = $this->scheduleService->getOrdersByDate($date);

        return response()->json([
            'success' => true,
            'date' => $date,
            'orders' => $orders
        ]);
    }
}
