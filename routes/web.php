<?php

use App\Http\Controllers\Other\NovaRegisterController;
use Illuminate\Support\Facades\Route;
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
    ->middleware('web')
    ->as('nova.')
    ->prefix(Nova::path())
    ->group(function () {
        Route::get('register', [NovaRegisterController::class, 'showRegistrationForm'])->name('register');
        Route::post('register', [NovaRegisterController::class, 'register']);
    });
