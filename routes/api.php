<?php

use App\Http\Controllers\V1\OfficePropertyController as V1OfficePropertyController;
use App\Http\Controllers\V1\PropertyController as V1PropertyController;
use App\Http\Controllers\V2\OfficePropertyController as V2OfficePropertyController;
use App\Http\Controllers\V2\PropertyController as V2PropertyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::get('properties', [V1PropertyController::class, 'index']);
    Route::get('properties/{property}', [V1PropertyController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('properties', [V1PropertyController::class, 'store']);
        Route::patch('properties/{property}', [V1PropertyController::class, 'update']);
        Route::delete('properties/{property}', [V1PropertyController::class, 'destroy']);
        Route::get('office/properties', [V1OfficePropertyController::class, 'index']);
    });
});

Route::prefix('v2')->group(function () {
    Route::get('properties', [V2PropertyController::class, 'index']);
    Route::get('properties/{property}', [V2PropertyController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('properties', [V2PropertyController::class, 'store']);
        Route::patch('properties/{property}', [V2PropertyController::class, 'update']);
        Route::delete('properties/{property}', [V2PropertyController::class, 'destroy']);
        Route::get('office/properties', [V2OfficePropertyController::class, 'index']);
    });
});
