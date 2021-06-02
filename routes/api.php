<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Implementations\ {
    BanquetController,
    BanquetStateController,
    SpaceController,
    TicketController,
    ServiceController,
    MenuController,
    ProductController,
    DiscountController,
    RoleController,
    UserController,
    CustomerController,
    CustomerFamilyMemberController,
    DatetimeController,
    PeriodController,
    CategoryController,
    OrderController,
    CommentController,
};

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

Route::flexibleResources([
    'banquets' => BanquetController::class,
    'banquet-states' => BanquetStateController::class,
    'menus' => MenuController::class,
    'products' => ProductController::class,
    'spaces' => SpaceController::class,
    'tickets' => TicketController::class,
    'services' => ServiceController::class,
    'discounts' => DiscountController::class,
    'customers' => CustomerController::class,
    'customer-family-members' => CustomerFamilyMemberController::class,
    'periods' => PeriodController::class,
    'datetimes' => DatetimeController::class,
    'comments' => CommentController::class,
    'roles' => RoleController::class,
    'users' => [UserController::class, [], function () {
        Route::post('/login', [UserController::class, 'login'])
            ->name('login')
            ->withoutMiddleware('auth.token');
    }],
    'orders' => [OrderController::class, ['prefix' => '{type}/orders']],
    'categories' => [CategoryController::class, ['prefix' => '{type}/categories']],
], ['middleware' => 'auth.token']);

Route::group(['middleware' => ['auth.token']], function () {
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
