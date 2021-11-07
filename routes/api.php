<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\User\UserController;

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

Route::prefix('auth')->group(function () {
    Route::post('login', [LoginController::class, 'handle'])->name('auth.login');
    Route::post('register', [RegisterController::class, 'handle'])->name('auth.register');
});

Route::middleware(['auth:api'])->group(function () {
    Route::prefix('user')->group(function () {
        Route::get('', [UserController::class, 'UserInfo'])->name('userInfo');
    });
});
