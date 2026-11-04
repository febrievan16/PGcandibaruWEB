<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventBookingController;
use App\Http\Controllers\OrderWebController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

// M1: Landing & Company Profile
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/profil', [PublicController::class, 'profile'])->name('profile');

// M2 & M3: Event Catalog & 2-Step Schedule-First Booking Wizard
Route::get('/events', [EventBookingController::class, 'index'])->name('events.index');
Route::get('/events/{slug}', [EventBookingController::class, 'show'])->name('events.show');
Route::post('/events/{slug}/book', [EventBookingController::class, 'store'])->name('events.book');

// M4: Guest/User Order Lookup, Checkout, Payment Simulator & Printable QR E-Ticket
Route::get('/orders/lookup', [OrderWebController::class, 'lookupForm'])->name('orders.lookup');
Route::post('/orders/lookup', [OrderWebController::class, 'lookupSubmit'])->name('orders.lookup.submit');
Route::get('/orders/{order_code}', [OrderWebController::class, 'show'])->name('orders.show');
Route::post('/orders/{order_code}/simulate-payment', [OrderWebController::class, 'simulatePayment'])->name('orders.simulate-payment');
Route::get('/orders/{order_code}/e-ticket', [OrderWebController::class, 'ticket'])->name('orders.ticket');

// M5: Optional Account Auth & Order History
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/akun/pesanan', [AuthController::class, 'myOrders'])->name('account.orders');
});

// M6: Admin Dashboard
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin'])
    ->group(function (): void {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::post('/notifications/read', [AdminDashboardController::class, 'markNotificationsRead'])->name('notifications.read');

        Route::get('/orders', [AdminDashboardController::class, 'orders'])->name('orders.index');
        Route::post('/orders/{order}/status', [AdminDashboardController::class, 'updateOrderStatus'])->name('orders.status');

        Route::get('/events', [AdminDashboardController::class, 'events'])->name('events.index');
        Route::get('/events/create', [AdminDashboardController::class, 'editEvent'])->name('events.create');
        Route::post('/events', [AdminDashboardController::class, 'saveEvent'])->name('events.store');
        Route::get('/events/{event}/edit', [AdminDashboardController::class, 'editEvent'])->name('events.edit');
        Route::put('/events/{event}', [AdminDashboardController::class, 'saveEvent'])->name('events.update');

        Route::post('/events/{event}/schedules', [AdminDashboardController::class, 'storeSchedule'])->name('schedules.store');
        Route::put('/schedules/{schedule}', [AdminDashboardController::class, 'updateSchedule'])->name('schedules.update');

        Route::get('/tickets/scanner', [AdminDashboardController::class, 'scanner'])->name('tickets.scanner');
        Route::post('/tickets/validate', [AdminDashboardController::class, 'validateTicketWeb'])->name('tickets.validate');

        Route::get('/profile', [AdminDashboardController::class, 'editProfile'])->name('profile.edit');
        Route::put('/profile', [AdminDashboardController::class, 'updateProfile'])->name('profile.update');
        Route::post('/profile/gallery', [AdminDashboardController::class, 'storeGallery'])->name('profile.gallery.store');
        Route::delete('/profile/gallery/{galleryItem}', [AdminDashboardController::class, 'deleteGallery'])->name('profile.gallery.delete');
    });
