<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\ForgotController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Controllers\Api\Auth\ResetPasswordController;
use App\Http\Controllers\Api\ClassRoom\ClassRoomController;
use App\Http\Controllers\Api\ClassRoom\ScheduleController;

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

Route::prefix('auth')->middleware(['guest'])->group(function () {
    Route::post('login', [LoginController::class, 'handle'])->name('auth.login');
    Route::post('forgot', [ForgotController::class, 'handle'])->name('auth.forgot');
    Route::post('register', [RegisterController::class, 'handle'])->name('auth.register');
    Route::post('reset-password', [ResetPasswordController::class, 'handle'])->name('auth.resetpassword');
});

Route::middleware(['auth:api'])->group(function () {
    Route::get('user', [UserController::class, 'UserInfo'])->name('userInfo');
    Route::prefix('classRoom')->group(function () {
        Route::prefix('schedule')->group(function () {
            Route::get('get/{idClass}', [ScheduleController::class, 'get'])->name('classRoom.schedule.get');
        });
        Route::get('list', [ClassRoomController::class, 'index'])->name('classRoom.index');
        Route::get('join/{idClass}', [ClassRoomController::class, 'join'])->name('classRoom.join');
        Route::get('leave/{idClass}', [ClassRoomController::class, 'leave'])->name('classRoom.leave');
        Route::post('add', [ClassRoomController::class, 'invite'])->name('classRoom.invite');
        Route::post('kick', [ClassRoomController::class, 'kick'])->name('classRoom.kick');
        Route::post('create', [ClassRoomController::class, 'create'])->name('classRoom.create');
        Route::delete('delete', [ClassRoomController::class, 'delete'])->name('classRoom.delete');
        Route::get('show/{idClass}', [ClassRoomController::class, 'show'])->name('classRoom.show');
    });
});
