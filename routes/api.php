<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Editor\CategoryEditorController;
use App\Http\Controllers\Editor\DishEditorController;
use App\Http\Controllers\Editor\MenuEditorController;
use App\Http\Controllers\Editor\RestaurantEditorController;
use App\Http\Controllers\Model\CategoryController;
use App\Http\Controllers\Model\CommentController;
use App\Http\Controllers\Model\DishCategoryController;
use App\Http\Controllers\Model\DishController;
use App\Http\Controllers\Model\DishMenuController;
use App\Http\Controllers\Model\DishVariantController;
use App\Http\Controllers\Model\MediaController;
use App\Http\Controllers\Model\MenuController;
use App\Http\Controllers\Model\ModelMediaController;
use App\Http\Controllers\Model\NotificationController;
use App\Http\Controllers\Model\ProductController;
use App\Http\Controllers\Model\RestaurantController;
use App\Http\Controllers\Model\RestaurantReviewController;
use App\Http\Controllers\Model\TagController;
use App\Http\Controllers\Model\UserController;
use App\Http\Controllers\Other\QueueController;
use App\Http\Controllers\Other\StatusController;
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

Route::group(['as' => 'api.'], function () {
    Route::get('status', [StatusController::class, 'check'])
        ->name('status');

    Route::get('status/list', [StatusController::class, 'list'])
        ->name('status.list');

    Route::prefix('dishes')
        ->name('dishes.')
        ->group(function () {
            Route::apiResource('menus', DishMenuController::class)
                ->only('index', 'show')
                ->parameters(['menus' => 'id'])
                ->middleware('cached:dish-menus');

            Route::apiResource('categories', DishCategoryController::class)
                ->only('index', 'show')
                ->parameters(['categories' => 'id'])
                ->middleware('cached:dish-categories');

            Route::apiResource('variants', DishVariantController::class)
                ->only('index', 'show')
                ->parameters(['variants' => 'id'])
                ->middleware('cached:dish-variants');
        });

    Route::apiResource('dishes', DishController::class)
        ->only('index', 'show')
        ->parameters(['dishes' => 'id'])
        ->middleware('cached:dishes');

    Route::apiResource('menus', MenuController::class)
        ->only('index', 'show')
        ->parameters(['menus' => 'id'])
        ->middleware('cached:menus');

    Route::apiResource('products', ProductController::class)
        ->only('index', 'show')
        ->parameters(['products' => 'id'])
        ->middleware('cached:products');

    Route::apiResource('tags', TagController::class)
        ->only('index', 'show')
        ->parameters(['tags' => 'id'])
        ->middleware('cached:tags');

    Route::apiResource('categories', CategoryController::class)
        ->only('index', 'show')
        ->parameters(['categories' => 'id'])
        ->middleware('cached:categories');

    Route::apiResource('restaurants', RestaurantController::class)
        ->only('index', 'show')
        ->parameters(['restaurants' => 'id'])
        ->middleware('cached:restaurants');

    Route::get('/restaurants/{id}/schedules', [RestaurantController::class, 'getSchedules'])
        ->name('restaurants.schedules')
        ->middleware('cached:restaurants');

    Route::get('/restaurants/{id}/holidays', [RestaurantController::class, 'getHolidays'])
        ->name('restaurants.holidays')
        ->middleware('cached:restaurants');

    Route::apiResource('restaurant-reviews', RestaurantReviewController::class)
        ->only('index', 'show', 'store')
        ->parameters(['restaurant-reviews' => 'id'])
        ->middleware('cached:restaurants');
});

Route::group(['middleware' => ['auth:signature,sanctum'], 'as' => 'api.'], function () {
    Route::post('queue/backup', [QueueController::class, 'backup'])
        ->name('queue.backup');
    Route::post('queue/alterations/perform', [QueueController::class, 'performAlternations'])
        ->name('queue.alterations.perform');
});

Route::group(['middleware' => 'auth:sanctum', 'as' => 'api.'], function () {
    Route::delete('/logout', LogoutController::class)->name('logout');

    Route::get('/users/me', [UserController::class, 'me'])->name('users.me');
    Route::post('/users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');
    Route::apiResource('users', UserController::class)
        ->only('index', 'show', 'store', 'update', 'destroy')
        ->parameters(['users' => 'id']);

    Route::apiResource('restaurants', RestaurantController::class)
        ->only('store', 'update')
        ->parameters(['restaurants' => 'id']);

    Route::post('/menus/attach-category', [MenuController::class, 'attachCategory'])
        ->name('menus.attach.category');
    Route::delete('/menus/detach-category', [MenuController::class, 'detachCategory'])
        ->name('menus.detach.category');

    Route::post('/menus/attach-product', [MenuController::class, 'attachProduct'])
        ->name('menus.attach.product');
    Route::delete('/menus/detach-product', [MenuController::class, 'detachProduct'])
        ->name('menus.detach.product');

    Route::apiResource('products', ProductController::class)
        ->only('store', 'update', 'destroy')
        ->parameters(['products' => 'id'])
        ->middleware('cached:products');

    Route::get('/notifications/poll', [NotificationController::class, 'poll'])->name('notifications.poll');
    Route::apiResource('notifications', NotificationController::class)
        ->only('index', 'show', 'store', 'update', 'destroy')
        ->parameters(['notifications' => 'id']);

    Route::apiResource('comments', CommentController::class)
        ->only('index', 'show', 'store', 'update', 'destroy')
        ->parameters(['comments' => 'id']);
});

Route::group(['middleware' => ['web', 'auth:sanctum,web'], 'as' => 'api.'], function () {
    Route::apiResource('media', MediaController::class)
        ->only('index', 'show', 'store', 'update', 'destroy')
        ->parameters(['media' => 'id']);

    Route::get('/model-media', [ModelMediaController::class, 'getModelMedia'])->name('media.get-model-media');
    Route::post('/model-media', [ModelMediaController::class, 'setModelMedia'])->name('media.set-model-media');
});

/*
 * The admin editor: everything of a restaurant in all languages (hidden and archived
 * menus, categories and dishes included). Signed in like the admin panel (session),
 * or with a token.
 */
Route::group([
    'middleware' => ['web', 'auth:sanctum,web'],
    'prefix' => 'editor',
    'as' => 'api.editor.',
], function () {
    Route::get('/restaurants', [RestaurantEditorController::class, 'index'])
        ->name('restaurants.index');
    Route::get('/restaurants/{id}', [RestaurantEditorController::class, 'show'])
        ->name('restaurants.show');
    Route::patch('/restaurants/{id}', [RestaurantEditorController::class, 'update'])
        ->name('restaurants.update');
    Route::put('/restaurants/{id}/notes', [RestaurantEditorController::class, 'updateNotes'])
        ->name('restaurants.notes');
    Route::put('/restaurants/{id}/photos', [RestaurantEditorController::class, 'updatePhotos'])
        ->name('restaurants.photos');
    Route::put('/restaurants/{id}/hours', [RestaurantEditorController::class, 'updateHours'])
        ->name('restaurants.hours');
    Route::post('/restaurants/{id}/menus', [MenuEditorController::class, 'store'])
        ->name('menus.store');
    Route::put('/restaurants/{id}/menus/order', [MenuEditorController::class, 'order'])
        ->name('menus.order');

    Route::patch('/menus/{id}', [MenuEditorController::class, 'update'])
        ->name('menus.update');
    Route::delete('/menus/{id}', [MenuEditorController::class, 'destroy'])
        ->name('menus.destroy');
    Route::post('/menus/{id}/archive', [MenuEditorController::class, 'archive'])
        ->name('menus.archive');
    Route::post('/menus/{id}/unarchive', [MenuEditorController::class, 'unarchive'])
        ->name('menus.unarchive');
    Route::post('/menus/{id}/duplicate', [MenuEditorController::class, 'duplicate'])
        ->name('menus.duplicate');
    Route::post('/menus/{id}/categories', [CategoryEditorController::class, 'store'])
        ->name('categories.store');

    Route::patch('/categories/{id}', [CategoryEditorController::class, 'update'])
        ->name('categories.update');
    Route::delete('/categories/{id}', [CategoryEditorController::class, 'destroy'])
        ->name('categories.destroy');
    Route::post('/categories/{id}/archive', [CategoryEditorController::class, 'archive'])
        ->name('categories.archive');
    Route::post('/categories/{id}/unarchive', [CategoryEditorController::class, 'unarchive'])
        ->name('categories.unarchive');
    Route::post('/categories/{id}/duplicate', [CategoryEditorController::class, 'duplicate'])
        ->name('categories.duplicate');
    Route::post('/categories/{id}/move', [CategoryEditorController::class, 'move'])
        ->name('categories.move');
    Route::post('/categories/{id}/dishes', [DishEditorController::class, 'store'])
        ->name('dishes.store');

    Route::patch('/dishes/{id}', [DishEditorController::class, 'update'])
        ->name('dishes.update');
    Route::delete('/dishes/{id}', [DishEditorController::class, 'destroy'])
        ->name('dishes.destroy');
    Route::post('/dishes/{id}/archive', [DishEditorController::class, 'archive'])
        ->name('dishes.archive');
    Route::post('/dishes/{id}/unarchive', [DishEditorController::class, 'unarchive'])
        ->name('dishes.unarchive');
    Route::post('/dishes/{id}/duplicate', [DishEditorController::class, 'duplicate'])
        ->name('dishes.duplicate');
    Route::post('/dishes/{id}/move', [DishEditorController::class, 'move'])
        ->name('dishes.move');
});

Route::fallback(function () {
    return ApiResponse::make([], 404, 'Not Found');
});
