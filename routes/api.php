<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LicenseSyncController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    // API Route for FlexLM script agent to post sync data
    Route::post('/licenses/sync', [LicenseSyncController::class, 'sync']);
});
