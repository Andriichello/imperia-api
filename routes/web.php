<?php

use App\Http\Controllers\Web\PreviewController;
use App\Http\Controllers\Web\WelcomeController;
use Illuminate\Support\Facades\Route;

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

Route::get('/', [WelcomeController::class, 'index'])
    ->name('welcome');

Route::get('/{restaurant_id}', [PreviewController::class, 'show'])
    ->name('restaurant.preview');

Route::get('/{restaurant_id}/menu/{menu_id?}', [PreviewController::class, 'show'])
    ->name('menu.preview');
