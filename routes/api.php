<?php

use App\Http\Controllers\V1\OfficePropertyController;
use App\Http\Controllers\V1\PropertyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {

    // Public — cursor-paginated for mobile scrolling
    // AvailableScope is active: only available properties visible
    Route::get('properties', [PropertyController::class, 'index']);

    // Public — resolves without AvailableScope so a shared link to any property works,
    // but the policy still allows view for anyone (viewAny/view both return true)
    Route::get('properties/{property}', [PropertyController::class, 'show']);

    // Protected — office and admin only
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('properties', [PropertyController::class, 'store']);
        Route::patch('properties/{property}', [PropertyController::class, 'update']);
        Route::delete('properties/{property}', [PropertyController::class, 'destroy']);

        // Office dashboard — regular pagination, all statuses for own office
        Route::get('office/properties', [OfficePropertyController::class, 'index']);
    });
});
