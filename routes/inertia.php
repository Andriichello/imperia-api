<?php

use App\Http\Controllers\Inertia\WelcomeController;
use App\Http\Controllers\Inertia\RestaurantController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Inertia Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', WelcomeController::class)->name('welcome');
Route::get('/{id}', [RestaurantController::class, 'show'])->name('');
