<?php

use App\Http\Controllers\Model\CategoryController;
use App\Http\Controllers\Model\MenuController;
use App\Http\Controllers\Model\ProductController;
use App\Http\Controllers\Model\ServiceController;
use App\Http\Controllers\Model\SpaceController;
use App\Http\Controllers\Model\TicketController;
use App\Http\Requests\Category\IndexCategoryRequest;
use App\Http\Requests\Menu\IndexMenuRequest;
use App\Http\Requests\Product\IndexProductRequest;
use App\Http\Requests\Service\IndexServiceRequest;
use App\Http\Requests\Space\IndexSpaceRequest;
use App\Http\Requests\Ticket\IndexTicketRequest;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tool API Routes
|--------------------------------------------------------------------------
|
| Here is where you may register API routes for your tool. These routes
| are loaded by the ServiceProvider of your tool. They are protected
| by your tool's "Authorize" middleware by default. Now, go build!
|
*/

Route::get('/menus', function (IndexMenuRequest $request) {
    /** @var MenuController $controller */
    $controller = app(MenuController::class);
    return $controller->index($request);
});

Route::get('/categories', function (IndexCategoryRequest $request) {
    $request->merge(['page' => ['size' => 100]]);

    /** @var CategoryController $controller */
    $controller = app(CategoryController::class);
    return $controller->index($request);
});

Route::get('/products', function (IndexProductRequest $request) {
    /** @var ProductController $controller */
    $controller = app(ProductController::class);
    return $controller->index($request);
});

Route::get('/spaces', function (IndexSpaceRequest $request) {
    /** @var SpaceController $controller */
    $controller = app(SpaceController::class);
    return $controller->index($request);
});

Route::get('/tickets', function (IndexTicketRequest $request) {
    /** @var TicketController $controller */
    $controller = app(TicketController::class);
    return $controller->index($request);
});

Route::get('/services', function (IndexServiceRequest $request) {
    /** @var ServiceController $controller */
    $controller = app(ServiceController::class);
    return $controller->index($request);
});
