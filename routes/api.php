<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EquipmentController;

Route::get('/ping', function() {
    return response()->json([
        'message' => 'API is working.'
    ]);
});

Route::post('/login', [AuthController::class, 'login']);
Route::get('/equipment', [EquipmentController::class, 'index']);
Route::get('/equipment/{equipment}', [EquipmentController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
