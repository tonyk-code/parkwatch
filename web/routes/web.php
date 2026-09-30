<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LiveParkingController;
use App\Http\Controllers\ParkingSessionController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\RateManagementController;
use App\Http\Controllers\SpotOverrideController;
use App\Http\Middleware\EnsureStaffAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::get('/dashboard', DashboardController::class)
    ->middleware([
        'auth',
        EnsureStaffAccess::class,
    ])
    ->name('dashboard');

Route::get('/live', function (Request $request) {
    $site = $request->user()
        ->accessibleSitesQuery()
        ->orderBy('name')
        ->firstOrFail();
    return redirect()->route('sites.live', $site);
})->middleware(['auth', EnsureStaffAccess::class,])->name('live');

Route::get('/sites/{site}/live', LiveParkingController::class)
    ->middleware(['auth', EnsureStaffAccess::class, 'can:view,site',])
    ->name('sites.live');

Route::post('/sites/{site}/spots/{spot}/override', SpotOverrideController::class)
    ->middleware(['auth', EnsureStaffAccess::class,])
    ->name('spots.override');

Route::get('/sessions', function (Request $request) {
    $site = $request->user()
        ->accessibleSitesQuery()
        ->orderBy('name')
        ->firstOrFail();

    return redirect()->route('sites.sessions', $site);
})->middleware([
            'auth',
            EnsureStaffAccess::class,
        ])->name('sessions');

Route::get('/sites/{site}/sessions', [
    ParkingSessionController::class,
    'index',
])->middleware([
            'auth',
            EnsureStaffAccess::class,
            'can:view,site',
        ])->name('sites.sessions');

Route::post('/sites/{site}/sessions', [
    ParkingSessionController::class,
    'store',
])->middleware([
            'auth',
            EnsureStaffAccess::class,
        ])->name('sites.sessions.store');

Route::post(
    "/sites/{site}/sessions/{session}/close",
    [ParkingSessionController::class, "close"]
)
    ->middleware([
        "auth",
        EnsureStaffAccess::class,
    ])
    ->name("sites.sessions.close");

Route::get('/rates', function (Request $request) {
    $site = $request->user()
        ->accessibleSitesQuery()
        ->orderBy('name')
        ->firstOrFail();

    return redirect()->route('sites.rates', $site);
})->middleware([
            'auth',
            EnsureStaffAccess::class,
        ])->name('rates');

Route::get(
    '/sites/{site}/rates',
    [RateManagementController::class, 'index'],
)
    ->middleware(['auth', EnsureStaffAccess::class, 'can:update,site'])
    ->name('sites.rates');

Route::post(
    '/sites/{site}/rates',
    [RateManagementController::class, 'update'],
)
    ->middleware(['auth', EnsureStaffAccess::class, 'can:update,site'])
    ->name('sites.rates.update');

Route::post(
    '/webhooks/payments/mock',
    PaymentWebhookController::class,
)->name('webhooks.payments.mock');