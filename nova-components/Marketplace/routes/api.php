<?php

use App\Http\Resources\Category\CategoryCollection;
use App\Http\Resources\Menu\MenuCollection;
use App\Http\Resources\Product\ProductCollection;
use App\Models\Menu;
use App\Models\Morphs\Category;
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

Route::get('/categories', function (Request $request) {
    $targets = ['products', 'tickets', 'spaces', 'services'];
    $target = $request->get('target');
    if (!in_array($target, $targets)) {
        $target = $targets[0];
    }

    $categories = Category::query()
        ->target($target)
        ->get();

    return ['categories' => new CategoryCollection($categories)];
});

Route::get('/products', function (Request $request) {
    $products = Product::query()->get();

    return ['products' => new ProductCollection($products)];
});
