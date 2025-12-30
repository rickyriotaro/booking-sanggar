<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CostumeController;
use App\Http\Controllers\Admin\DanceServiceController;
use App\Http\Controllers\Admin\MakeupServiceController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ChatSupportController;
use App\Http\Controllers\Admin\ScheduleController;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Costumes
    Route::resource('costumes', CostumeController::class);
    
    // Dance Services
    Route::resource('dance-services', DanceServiceController::class);
    
    // Makeup Services
    Route::resource('makeup-services', MakeupServiceController::class);
    
    // Orders
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::patch('orders/{order}/return-status', [OrderController::class, 'updateReturnStatus'])->name('orders.update-return-status');
    Route::get('orders/export/excel', [OrderController::class, 'exportExcel'])->name('orders.export.excel');
    Route::get('orders/export/pdf', [OrderController::class, 'exportPdf'])->name('orders.export.pdf');
    
    // Transactions
    Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::get('transactions-report', [TransactionController::class, 'report'])->name('transactions.report');
    Route::get('transactions/export/excel', [TransactionController::class, 'exportExcel'])->name('transactions.export.excel');
    Route::get('transactions/export/pdf', [TransactionController::class, 'exportPdf'])->name('transactions.export.pdf');
    
    // Gallery
    Route::resource('gallery', GalleryController::class);
    
    // Users
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::patch('users/{user}/role', [UserController::class, 'updateRole'])->name('users.update-role');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    
    // Profile
    Route::get('profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    
    // Chat Support
    Route::get('chat-support', [ChatSupportController::class, 'index'])->name('chat-support.index');
    Route::get('chat-support/{chatSession}', [ChatSupportController::class, 'show'])->name('chat-support.show');
    Route::post('chat-support/{chatSession}/message', [ChatSupportController::class, 'sendMessage'])->name('chat-support.send');
    Route::post('chat-support/{chatSession}/assign', [ChatSupportController::class, 'assignToMe'])->name('chat-support.assign');
    Route::get('chat-support/{chatSession}/get-messages', [ChatSupportController::class, 'getMessages'])->name('chat-support.get-messages');
    Route::patch('chat-support/{chatSession}/close', [ChatSupportController::class, 'close'])->name('chat-support.close');
    
    // Schedule & Calendar
    Route::get('schedule', [ScheduleController::class, 'index'])->name('schedule.index');
    Route::get('schedule/events', [ScheduleController::class, 'getEvents'])->name('schedule.events');
    Route::get('schedule/booked-dates', [ScheduleController::class, 'getBookedDates'])->name('schedule.booked-dates');
    Route::get('schedule/orders-by-date', [ScheduleController::class, 'getOrdersByDate'])->name('schedule.orders-by-date');
    
    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

// Redirect root to login
Route::get('/', function () {
    return redirect('/login');
});
