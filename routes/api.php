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
    'discounts' => \App\Http\Controllers\Implementations\DiscountController::class,
    'roles' => \App\Http\Controllers\Implementations\RoleController::class,
    'customers' => \App\Http\Controllers\Implementations\CustomerController::class,
    'customer-family-members' => \App\Http\Controllers\Implementations\CustomerFamilyMemberController::class,
    'datetimes' => \App\Http\Controllers\Implementations\DatetimeController::class,
    'periods' => \App\Http\Controllers\Implementations\PeriodController::class,
];

foreach ($controllers as $modelName => $controller) {
    $root = '/' . $modelName;

    Route::get($root, [$controller, 'index'])
        ->name($modelName . '.index');

    Route::get($root . '/{id}', [$controller, 'show'])
        ->name($modelName . '.show');

    Route::post($root, [$controller, 'store'])
        ->name($modelName . '.store');

    Route::patch($root . '/{id?}', [$controller, 'update'])
        ->name($modelName . '.update');

    Route::delete($root . '/{id?}', [$controller, 'destroy'])
        ->name($modelName . '.destroy');
}

Route::group([], function () {
    $controller = \App\Http\Controllers\Implementations\UserController::class;

    $modelName = 'users';
    $root = '/' . $modelName;
    Route::get($root, [$controller, 'index'])
        ->name($modelName . '.index');

    Route::get($root . '/{id}', [$controller, 'show'])
        ->name($modelName . '.show');

    Route::post($root . '/login', [$controller, 'login'])
        ->name($modelName . '.login');

    Route::post($root, [$controller, 'store'])
        ->name($modelName . '.store');

    Route::patch($root . '/{id?}', [$controller, 'update'])
        ->name($modelName . '.update');

    Route::delete($root . '/{id?}', [$controller, 'destroy'])
        ->name($modelName . '.destroy');
});

Route::group([], function () {
    $controller = \App\Http\Controllers\Implementations\CategoryController::class;
    Route::get('/{type}-categories', [$controller, 'index']);
    Route::get('/{type}-categories/{id}', [$controller, 'show']);
    Route::post('/{type}-categories', [$controller, 'store']);
    Route::patch('/{type}-categories/{id?}', [$controller, 'update']);
    Route::delete('/{type}-categories/{id?}', [$controller, 'destroy']);
});

Route::group([], function () {
    $controller = \App\Http\Controllers\Implementations\OrderController::class;
    Route::get('/{type}-orders', [$controller, 'index']);
    Route::get('/{type}-orders/{id}', [$controller, 'show']);
    Route::post('/{type}-orders', [$controller, 'store']);
    Route::patch('/{type}-orders/{id?}', [$controller, 'update']);
    Route::delete('/{type}-orders/{id?}', [$controller, 'destroy']);
});

Route::group([], function () {
    $controller = \App\Http\Controllers\Implementations\CommentController::class;

    $modelName = 'comments';
    $root = '/' . $modelName;
    Route::get($root, [$controller, 'index'])
        ->name($modelName . '.index');

    Route::post($root, [$controller, 'store'])
        ->name($modelName . '.store');

    Route::patch($root, [$controller, 'update'])
        ->name($modelName . '.update');

    Route::delete($root, [$controller, 'destroy'])
        ->name($modelName . '.destroy');
});

Route::group([], function () {
    Route::get('/routes', function () {
        $routes = [];
        foreach (Route::getRoutes() as $route) {
            if (str_starts_with($route->uri, '_ignition')) {
                continue;
            }

            if (isset($route->action['controller']) && str_contains($route->action['controller'], 'App\\Http\\Controllers\\Implementations\\')) {
                $controllerClass = $route->action['controller'];
                $position = strpos($controllerClass, '@');
                if ($position !== false) {
                    $controllerClass = substr($controllerClass, 0, $position);
                }

                $controller = new $controllerClass();
                if ($controller instanceof \App\Http\Controllers\DynamicController) {
                    $attributes = $controller->fillable();
                }
            }

            $routes[] = [
                'route' => $route->uri,
                'methods' => $route->methods,
                'attributes' => $attributes ?? [],
            ];
        }
        return ['success' => true, 'routes' => $routes];
    })->name('routes.index');
});
