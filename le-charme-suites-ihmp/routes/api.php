<?php

use App\Http\Controllers\ApiAuthController;
use App\Http\Controllers\IntegrationApiController;
use App\Http\Controllers\OnlineBookingApiController;
use App\Http\Controllers\StaffRoomApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/auth')->group(function () {
    Route::post('/login', [ApiAuthController::class, 'login'])->middleware('throttle:10,1')->name('api.v1.auth.login');
    Route::post('/refresh', [ApiAuthController::class, 'refresh'])->middleware('throttle:20,1')->name('api.v1.auth.refresh');

    Route::middleware('jwt.auth')->group(function () {
        Route::get('/me', [ApiAuthController::class, 'me'])->name('api.v1.auth.me');
        Route::post('/logout', [ApiAuthController::class, 'logout'])->name('api.v1.auth.logout');
    });
});

Route::prefix('v1/staff')->middleware('jwt.auth')->group(function () {
    Route::get('/rooms', StaffRoomApiController::class)
        ->middleware('permission:rooms.read')
        ->name('api.v1.staff.rooms');
});

Route::prefix('v1')->middleware('integration.key')->group(function () {
    Route::get('/status', [IntegrationApiController::class, 'status'])->name('api.v1.status');
    Route::get('/datasets/{dataset}', [IntegrationApiController::class, 'dataset'])->name('api.v1.datasets.show');
    Route::post('/bookings', [OnlineBookingApiController::class, 'store'])
        ->middleware('throttle:30,1')
        ->name('api.v1.bookings.store');
    Route::put('/bookings/{externalReference}', [OnlineBookingApiController::class, 'update'])
        ->middleware('throttle:30,1')
        ->name('api.v1.bookings.update');
    Route::post('/bookings/{externalReference}/cancel', [OnlineBookingApiController::class, 'cancel'])
        ->middleware('throttle:30,1')
        ->name('api.v1.bookings.cancel');
});
