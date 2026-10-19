<?php

use App\Http\Controllers\IntegrationApiController;
use App\Http\Controllers\OnlineBookingApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('integration.key')->group(function () {
    Route::get('/status', [IntegrationApiController::class, 'status'])->name('api.v1.status');
    Route::get('/datasets/{dataset}', [IntegrationApiController::class, 'dataset'])->name('api.v1.datasets.show');
    Route::post('/bookings', [OnlineBookingApiController::class, 'store'])
        ->middleware('throttle:30,1')
        ->name('api.v1.bookings.store');
});
