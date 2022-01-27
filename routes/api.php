<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Model\CustomerController;
use App\Http\Controllers\Model\FamilyMemberController;
use App\Http\Controllers\Model\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/register', RegisterController::class)->name('api.register');
Route::post('/login', LoginController::class)->name('api.login');

Route::group(['as' => 'api.', 'middleware' => []], function () {
    Route::delete('/logout', LogoutController::class)->name('logout');

    Route::get('/users/me', [UserController::class, 'me'])->name('users.me');
    Route::post('/users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
    Route::apiResource('users', UserController::class)
        ->only('index', 'show', 'store', 'update', 'destroy')
        ->parameters(['users' => 'id']);

    Route::post('/customers/{id}/restore', [CustomerController::class, 'restore'])->name('customers.restore');
    Route::apiResource('customers', CustomerController::class)
        ->only('index', 'show', 'store', 'update', 'destroy')
        ->parameters(['customers' => 'id']);

    Route::apiResource('family-members', FamilyMemberController::class)
        ->only('index', 'show', 'store', 'update', 'destroy')
        ->parameters(['family-members' => 'id']);
});

Route::fallback(function () {
    abort(404, 'Not found');
});
