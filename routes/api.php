<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\GalleryController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\StockSnapshotController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\ScheduleController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public catalog
Route::get('/costumes', [CatalogController::class, 'costumes']);
Route::get('/costumes/{id}', [CatalogController::class, 'costumeDetail']);
Route::get('/costumes/{id}/next-availability', [CatalogController::class, 'getCostumeNextAvailability']);
Route::get('/dance-services', [CatalogController::class, 'danceServices']);
Route::get('/dance-services/{id}', [CatalogController::class, 'danceServiceDetail']);
Route::get('/makeup-services', [CatalogController::class, 'makeupServices']);
Route::get('/makeup-services/{id}', [CatalogController::class, 'makeupServiceDetail']);
Route::get('/makeup-services/{id}/next-availability', [CatalogController::class, 'getMakeupNextAvailability']);
Route::get('/gallery', [GalleryController::class, 'index']);

// Schedule & Availability (Public - for browsing)
Route::post('/schedule/check-availability', [ScheduleController::class, 'checkAvailability']);
Route::get('/schedule/events', [ScheduleController::class, 'getEvents']);
Route::get('/schedule/month-availability', [ScheduleController::class, 'getMonthAvailability']);

// Availability checking endpoints (untuk Flutter date blocking)
Route::get('/booked-dates/{serviceType}/{serviceId}', [AvailabilityController::class, 'getBookedDates']);
Route::post('/check-availability', [AvailabilityController::class, 'checkAvailability']);

// NEW: Date-based availability endpoints dengan calculation overlapping bookings
Route::get('/availability/{serviceType}/{serviceId}', [AvailabilityController::class, 'getSummary']);
Route::get('/availability/{serviceType}/{serviceId}/on-date', [AvailabilityController::class, 'getOnDate']);
Route::get('/availability/{serviceType}/{serviceId}/next', [AvailabilityController::class, 'getNextAvailable']);
Route::get('/availability/{serviceType}/{serviceId}/returning', [AvailabilityController::class, 'getReturning']);
Route::get('/availability/{serviceType}/{serviceId}/real-time', [AvailabilityController::class, 'getRealTimeStock']);
Route::get('/availability/{serviceType}/{serviceId}/history', [AvailabilityController::class, 'getAdminHistory']);

// Stock Snapshot API (Public - untuk Flutter cek real-time stok)
Route::get('/stock-snapshot/{serviceType}/{serviceId}', [StockSnapshotController::class, 'getSnapshot']);
Route::get('/stock-snapshot/{serviceType}/{serviceId}/history', [StockSnapshotController::class, 'getHistory']);

// Stock Snapshot Admin (Update stock - require auth)
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('/stock-snapshot/{serviceType}/{serviceId}/update-stock', [StockSnapshotController::class, 'updateStock']);
    Route::post('/stock-snapshot/recalculate-all', [StockSnapshotController::class, 'recalculateAll']);
    Route::post('/stock-snapshot/initialize-all', [StockSnapshotController::class, 'initializeAll']);
});

// Midtrans Webhook (No Auth Required)
Route::post('/payment/notification', [PaymentController::class, 'handleNotification']);
Route::post('/payment/webhook', [PaymentController::class, 'handleNotification']);

// Protected routes (Customer only)
Route::middleware(['auth:sanctum', 'customer'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile/update', [ProfileController::class, 'update']);
    Route::post('/change-password', [ProfileController::class, 'changePassword']);

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);
    Route::post('/orders/check-time-conflict', [OrderController::class, 'checkTimeConflict']); // Check time slot conflicts for Jasa Tari

    // Validate order before creation
    Route::post('/schedule/validate-order', [ScheduleController::class, 'validateOrder']);

    // Check stock availability
    Route::post('/check-stock', [CatalogController::class, 'checkStock']);
    Route::post('/check-dance-slots', [CatalogController::class, 'checkDanceSlots']);
    Route::post('/check-makeup-slots', [CatalogController::class, 'checkMakeupSlots']);

    // Addresses
    Route::get('/addresses', [AddressController::class, 'index']);
    Route::get('/addresses/primary', [AddressController::class, 'getPrimary']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::get('/addresses/{id}', [AddressController::class, 'show']);
    Route::put('/addresses/{id}', [AddressController::class, 'update']);
    Route::delete('/addresses/{id}', [AddressController::class, 'destroy']);
    Route::post('/addresses/{id}/set-primary', [AddressController::class, 'setPrimary']);

    // Reviews (per-item reviews - new structure)
    Route::post('/orders/{orderId}/items/{itemId}/review', [ReviewController::class, 'store']);
    Route::get('/orders/{orderId}/items/{itemId}/review', [ReviewController::class, 'getItemReview']);
    Route::get('/orders/{orderId}/reviews', [ReviewController::class, 'getOrderReviews']);
    Route::get('/my-reviews', [ReviewController::class, 'myReviews']);

    // Payment
    Route::post('/payment/create', [PaymentController::class, 'createPayment']); // DEPRECATED - gunakan initiatePayment
    Route::post('/payment/initiate', [PaymentController::class, 'initiatePayment']); // NEW - Custom payment UI
    Route::get('/payment/methods', [PaymentController::class, 'getPaymentMethods']); // NEW - Get payment methods list
    Route::get('/payment/detail/{orderId}', [PaymentController::class, 'getPaymentDetail']); // NEW - Get payment detail
    Route::get('/payment/snap-token/{orderId}', [PaymentController::class, 'getSnapToken']); // For Flutter Midtrans (if exists)
    Route::get('/payment/status/{orderId}', [PaymentController::class, 'checkStatus']);
    Route::get('/payment/verify/{orderId}', [PaymentController::class, 'checkStatus']); // Alias for verify

    // AI Chat Support
    Route::get('/chat', [ChatController::class, 'index']);
    Route::post('/chat/send', [ChatController::class, 'sendMessage']);
    Route::post('/chat/request-human', [ChatController::class, 'requestHumanSupport']);
    Route::delete('/chat', [ChatController::class, 'deleteChat']); // Delete chat
    Route::post('/chat/keep-alive', [ChatController::class, 'keepAlive']); // Keep session active
    Route::post('/chat/close-session', [ChatController::class, 'closeSession']); // User close chat

    // Notifications - Push notification management
    Route::post('/notifications/fcm-token', [NotificationController::class, 'storeFcmToken']); // Store FCM token from mobile
    Route::get('/notifications', [NotificationController::class, 'getNotifications']); // Get user's notifications (paginated)
    Route::get('/notifications/unread', [NotificationController::class, 'unread']); // Get unread notifications only
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead']); // Mark notification as read
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']); // Mark all as read
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']); // Delete notification
});
