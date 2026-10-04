<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CustomerPaymentController;
use App\Http\Controllers\Api\ReservationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CustomerSessionController;

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'user_type' => $user->user_type->value,
        ]);
    });

    Route::get(
        '/customer/sessions',
        [CustomerSessionController::class, 'index'],
    );

    Route::get(
        '/customer/sessions/{session}/payment',
        [CustomerPaymentController::class, 'show'],
    );

    Route::post('/customer/payments', [CustomerPaymentController::class, 'store']);

    Route::get(
        '/customer/payments/{payment}',
        [CustomerPaymentController::class, 'showPayment']
    );

    Route::post(
        '/customer/reservations',
        [ReservationController::class, 'store']
    );
});