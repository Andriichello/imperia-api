<?php

use App\Http\Controllers\Inertia\PreviewController;
use App\Http\Controllers\Inertia\WelcomeController;
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


Route::get('/{restaurant_id}', [PreviewController::class, 'show'])
    ->name('restaurant.preview');
Route::get('/{restaurant_id}/menu/{menu_id?}', [PreviewController::class, 'show'])
    ->name('menu.preview');

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where the routes that are called from Inertia UI should be placed.
*/
