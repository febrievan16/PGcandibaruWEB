<?php

use App\Http\Controllers\Api\V1\AdminApiController;
use App\Http\Controllers\Api\V1\EventScheduleApiController;
use App\Http\Controllers\Api\V1\OrderApiController;
use App\Http\Controllers\Api\V1\PaymentNotificationApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/events/{event_slug}/schedules', [EventScheduleApiController::class, 'show'])
        ->name('api.v1.events.schedules');

    Route::post('/orders', [OrderApiController::class, 'store'])
        ->middleware('api.auth:optional')
        ->name('api.v1.orders.store');

    Route::post('/payments/notification', PaymentNotificationApiController::class)
        ->name('api.v1.payments.notification');

    Route::get('/orders/{order_code}', [OrderApiController::class, 'show'])
        ->middleware('api.auth:optional')
        ->name('api.v1.orders.show');

    Route::prefix('admin')
        ->middleware(['api.auth:required', 'admin'])
        ->group(function (): void {
            Route::get('/orders', [AdminApiController::class, 'orders'])
                ->name('api.v1.admin.orders');

            Route::post('/tickets/validate', [AdminApiController::class, 'validateTicket'])
                ->name('api.v1.admin.tickets.validate');
        });
});

