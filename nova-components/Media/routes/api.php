<?php

use App\Http\Controllers\Model\MediaController;
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

Route::apiResource('items', MediaController::class)
    ->only('index', 'show', 'store', 'update', 'destroy')
    ->parameters(['items' => 'id']);
