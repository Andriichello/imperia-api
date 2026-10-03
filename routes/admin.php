<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PasswordResetController;
use App\Http\Controllers\Admin\SignInController;
use App\Http\Controllers\Editor\EditorPageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| The restaurant admin: sign in, the dashboard and the menu editor, under `/admin`
| (the admin panel itself is at `/admin/manage`). Signed in with the session.
|
*/

Route::get('/login', [SignInController::class, 'show'])
    ->name('login');
Route::post('/login', [SignInController::class, 'store'])
    ->name('login.store');
Route::post('/logout', [SignInController::class, 'destroy'])
    ->name('logout');

Route::get('/forgot-password', [PasswordResetController::class, 'create'])
    ->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'store'])
    ->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])
    ->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'update'])
    ->name('password.update');

Route::middleware('auth:web')->group(function () {
    Route::get('/', [DashboardController::class, 'show'])
        ->name('dashboard');

    Route::get('/editor', [EditorPageController::class, 'index'])
        ->name('editor.index');
    Route::get('/editor/{id}', [EditorPageController::class, 'show'])
        ->whereNumber('id')
        ->name('editor');
});
