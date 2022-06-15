<?php

use App\Http\Controllers\Model\MenuController;
use App\Http\Controllers\Model\ProductController;
use App\Http\Requests\Menu\IndexMenuRequest;
use App\Http\Requests\Product\IndexProductRequest;
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

Route::get('/products', function (IndexProductRequest $request) {
    /** @var ProductController $controller */
    $controller = app(ProductController::class);
    return $controller->index($request);
});
