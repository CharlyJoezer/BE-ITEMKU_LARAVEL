<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ShopController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\RegisterController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::POST('/auth/login', [AuthController::class, 'createLoginToken']);
Route::POST('/auth/register', [RegisterController::class, 'processRegister']);
Route::GET('/auth/get-user-data', [UserController::class, 'getUserData']);
Route::POST('/auth/logout', [AuthController::class, 'logoutAndDeleteToken']);

Route::POST('/shop/create', [ShopController::class, 'createShop']);