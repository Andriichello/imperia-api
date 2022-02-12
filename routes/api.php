<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Model\CategoryController;
use App\Http\Controllers\Model\CommentController;
use App\Http\Controllers\Model\CustomerController;
use App\Http\Controllers\Model\FamilyMemberController;
use App\Http\Controllers\Model\MenuController;
use App\Http\Controllers\Model\ProductController;
use App\Http\Controllers\Model\ServiceController;
use App\Http\Controllers\Model\SpaceController;
use App\Http\Controllers\Model\TicketController;
use App\Http\Controllers\Model\UserController;
use App\Http\Responses\ApiResponse;
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

Route::group(['middleware' => 'auth:sanctum', 'as' => 'api.'], function () {
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

    Route::apiResource('menus', MenuController::class)
        ->only('index', 'show')
        ->parameters(['menus' => 'id']);

    Route::apiResource('products', ProductController::class)
        ->only('index', 'show')
        ->parameters(['products' => 'id']);

    Route::apiResource('tickets', TicketController::class)
        ->only('index', 'show')
        ->parameters(['tickets' => 'id']);

    Route::apiResource('services', ServiceController::class)
        ->only('index', 'show')
        ->parameters(['services' => 'id']);

    Route::apiResource('spaces', SpaceController::class)
        ->only('index', 'show')
        ->parameters(['spaces' => 'id']);

    Route::apiResource('comments', CommentController::class)
        ->only('index', 'show', 'store', 'update', 'destroy')
        ->parameters(['comments' => 'id']);

    Route::apiResource('categories', CategoryController::class)
        ->only('index', 'show')
        ->parameters(['categories' => 'id']);
});

Route::fallback(function () {
    return ApiResponse::make([], 404, 'Not Found');
});
