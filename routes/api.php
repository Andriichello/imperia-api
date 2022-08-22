<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Model\BanquetController;
use App\Http\Controllers\Model\CategoryController;
use App\Http\Controllers\Model\CommentController;
use App\Http\Controllers\Model\CustomerController;
use App\Http\Controllers\Model\FamilyMemberController;
use App\Http\Controllers\Model\MediaController;
use App\Http\Controllers\Model\MenuController;
use App\Http\Controllers\Model\ModelMediaController;
use App\Http\Controllers\Model\NotificationController;
use App\Http\Controllers\Model\OrderController;
use App\Http\Controllers\Model\ProductController;
use App\Http\Controllers\Model\RestaurantController;
use App\Http\Controllers\Model\ScheduleController;
use App\Http\Controllers\Model\ServiceController;
use App\Http\Controllers\Model\SpaceController;
use App\Http\Controllers\Model\TicketController;
use App\Http\Controllers\Model\UserController;
use App\Http\Controllers\Other\InvoiceController;
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

    Route::apiResource('restaurants', RestaurantController::class)
        ->only('index', 'show')
        ->parameters(['restaurants' => 'id']);

    Route::apiResource('schedules', ScheduleController::class)
        ->only('index', 'show')
        ->parameters(['schedules' => 'id']);

    Route::get('/notifications/poll', [NotificationController::class, 'poll'])->name('notifications.poll');
    Route::apiResource('notifications', NotificationController::class)
        ->only('index', 'show', 'store', 'update', 'destroy')
        ->parameters(['notifications' => 'id']);

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

    Route::get('/spaces/reservations', [SpaceController::class, 'reservations'])->name('spaces.reservations');
    Route::apiResource('spaces', SpaceController::class)
        ->only('index', 'show')
        ->parameters(['spaces' => 'id']);

    Route::apiResource('comments', CommentController::class)
        ->only('index', 'show', 'store', 'update', 'destroy')
        ->parameters(['comments' => 'id']);

      Route::apiResource('media', MediaController::class)
        ->only('index', 'show', 'store', 'update', 'destroy')
        ->parameters(['media' => 'id']);

    Route::get('/model-media', [ModelMediaController::class, 'getModelMedia'])->name('media.get-model-media');
    Route::post('/model-media', [ModelMediaController::class, 'setModelMedia'])->name('media.set-model-media');

    Route::apiResource('categories', CategoryController::class)
        ->only('index', 'show')
        ->parameters(['categories' => 'id']);

    Route::post('/orders/{id}/restore', [OrderController::class, 'restore'])->name('orders.restore');
    Route::apiResource('orders', OrderController::class)
        ->only('index', 'show', 'store', 'update', 'destroy')
        ->parameters(['orders' => 'id']);

    Route::get('/banquets/{id}/order', [OrderController::class, 'showByBanquetId']);
    Route::post('/banquets/{id}/restore', [BanquetController::class, 'restore'])->name('banquets.restore');
    Route::apiResource('banquets', BanquetController::class)
        ->only('index', 'show', 'store', 'update', 'destroy')
        ->parameters(['banquets' => 'id']);
});

Route::group(['middleware' => ['auth:signature,sanctum'], 'as' => 'api.'], function () {
    Route::get('/orders/{id}/invoice', [InvoiceController::class, 'view'])->name('orders.invoice');
    Route::get('/orders/{id}/invoice/pdf', [InvoiceController::class, 'pdf'])->name('orders.invoice-pdf');

    Route::get('/banquets/{id}/invoice', [InvoiceController::class, 'viewThroughBanquet'])
        ->name('banquets.invoice');
    Route::get('/banquets/{id}/invoice/pdf', [InvoiceController::class, 'pdfThroughBanquet'])
        ->name('banquets.invoice-pdf');
});

Route::fallback(function () {
    return ApiResponse::make([], 404, 'Not Found');
});
