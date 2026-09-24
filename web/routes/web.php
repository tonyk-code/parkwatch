<?php

use Illuminate\Support\Facades\Route;
use App\Models\Site;
use Inertia\Inertia;
use App\Http\Middleware\EnsureStaffAccess;

Route::inertia('/', 'welcome')->name('home');

Route::get('/dashboard', function () {
    return Inertia::render('dashboard');
})->middleware(['auth', EnsureStaffAccess::class,])->name('dashboard');

Route::get('/sites/{site}/dashboard', function (Site $site) {
    return Inertia::render('site/dashboard', [
        'site' => [
            'id' => $site->id,
            'name' => $site->name,
            'code' => $site->code,
        ],
    ]);
})->middleware([
            'auth',
            EnsureStaffAccess::class,
            'can:view,site',
        ])->name('site.dashboard');