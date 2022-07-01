<?php

use App\Http\Controllers\Nova\LoginController;
use App\Http\Controllers\Nova\RegisterController;
use Illuminate\Support\Facades\Route;
use Laravel\Nova\Http\Middleware\RedirectIfAuthenticated;
use Laravel\Nova\Nova;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::domain(config('nova.domain', null))
    ->middleware(['web', RedirectIfAuthenticated::class])
    ->as('nova.')
    ->prefix(Nova::path())
    ->group(function () {
        Route::get('auth', [LoginController::class, 'showLoginForm'])->name('auth');
        Route::post('auth', [LoginController::class, 'login']);

        Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
        Route::post('register', [RegisterController::class, 'register']);
    });
