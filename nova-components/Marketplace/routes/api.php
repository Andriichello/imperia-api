<?php

use App\Http\Resources\Menu\MenuCollection;
use App\Http\Resources\Product\ProductCollection;
use App\Models\Menu;
use App\Models\Product;
use Illuminate\Http\Request;
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

Route::get('/menus', function () {
    $menus = Menu::query()
        ->archived(false)
        ->get();

    return ['menus' => new MenuCollection($menus)];
});

Route::get('/products', function (Request $request) {
    $query = Product::query();

    if ($request->has('menu_id')) {
        $query->withMenu((int) $request->get('menu_id'));
    }
    if ($request->has('category_id')) {
        $query->withAllOfCategories((int) $request->get('category_id'));
    }

    return ['products' => new ProductCollection($query->get())];
});
