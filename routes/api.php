<?php

use Illuminate\Http\Request;
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

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

$controllers = [
    'banquets' => \App\Http\Controllers\Implementations\BanquetController::class,
    'banquet-states' => \App\Http\Controllers\Implementations\BanquetStateController::class,
    'spaces' => \App\Http\Controllers\Implementations\SpaceController::class,
    'tickets' => \App\Http\Controllers\Implementations\TicketController::class,
    'services' => \App\Http\Controllers\Implementations\ServiceController::class,
    'menus' => \App\Http\Controllers\Implementations\MenuController::class,
    'products' => \App\Http\Controllers\Implementations\ProductController::class,
    'roles' => \App\Http\Controllers\Implementations\RoleController::class,
    'users' => \App\Http\Controllers\Implementations\UserController::class,
    'customers' => \App\Http\Controllers\Implementations\CustomerController::class,
    'customer-family-members' => \App\Http\Controllers\Implementations\CustomerFamilyMemberController::class,
];

foreach ($controllers as $modelName => $controller) {
    $root = '/' . $modelName;

    Route::get($root, [$controller, 'index'])
        ->name($modelName . '.index');

    Route::get($root . '/{id}', [$controller, 'show'])
        ->name($modelName . '.show');

    Route::post($root, [$controller, 'create'])
        ->name($modelName . '.create');

    Route::patch($root . '/{id?}', [$controller, 'update'])
        ->name($modelName . '.update');

    Route::delete($root . '/{id?}', [$controller, 'delete'])
        ->name($modelName . '.delete');
}

Route::group([], function () {
    $controller = \App\Http\Controllers\Implementations\CategoryController::class;
    Route::get('/{type}-categories', [$controller, 'index']);
    Route::get('/{type}-categories/{id}', [$controller, 'show']);
    Route::post('/{type}-categories', [$controller, 'create']);
    Route::patch('/{type}-categories/{id?}', [$controller, 'update']);
    Route::delete('/{type}-categories/{id?}', [$controller, 'delete']);
});

Route::group([], function () {
    $controller = \App\Http\Controllers\Implementations\CommentController::class;

    $modelName = 'comments';
    $root = '/' . $modelName;
    Route::get($root, [$controller, 'index'])
        ->name($modelName . '.index');

    Route::post($root, [$controller, 'create'])
        ->name($modelName . '.create');

    Route::patch($root, [$controller, 'update'])
        ->name($modelName . '.update');

    Route::delete($root, [$controller, 'delete'])
        ->name($modelName . '.delete');
});
