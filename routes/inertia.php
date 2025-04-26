<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Inertia\WelcomeController;
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
use App\Http\Controllers\Model\RestaurantReviewController;
use App\Http\Controllers\Model\ServiceController;
use App\Http\Controllers\Model\SpaceController;
use App\Http\Controllers\Model\TagController;
use App\Http\Controllers\Model\TicketController;
use App\Http\Controllers\Model\UserController;
use App\Http\Controllers\Model\WaiterController;
use App\Http\Controllers\Other\InvoiceController;
use App\Http\Controllers\Other\MetricsController;
use App\Http\Controllers\Other\QueueController;
use App\Http\Controllers\Other\StatusController;
use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Inertia Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', WelcomeController::class)->name('welcome');
