<?php

use App\Http\Controllers\IntegrationApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('integration.key')->group(function () {
    Route::get('/status', [IntegrationApiController::class, 'status'])->name('api.v1.status');
    Route::get('/datasets/{dataset}', [IntegrationApiController::class, 'dataset'])->name('api.v1.datasets.show');
});
