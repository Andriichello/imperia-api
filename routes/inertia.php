<?php

use App\Http\Controllers\Inertia\MenuController;
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

Route::get('/{restaurant_id}', [RestaurantController::class, 'show'])
    ->name('restaurant.show');
Route::get('/{restaurant_id}/menu/{menu_id?}', [MenuController::class, 'show'])
    ->name('menu.show');



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where the routes that are called from Inertia UI should be placed.
*/

Route::get('/{restaurant_id}/menus', [MenuController::class, 'menusWithProducts'])
    ->name('menu.load');
