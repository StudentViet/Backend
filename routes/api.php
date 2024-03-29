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
use App\Http\Controllers\Api\ClassRoom\ExamController;
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
    Route::get('check-token/{token}', [ResetPasswordController::class, 'check'])->name('auth.checktoken');
    Route::post('reset-password', [ResetPasswordController::class, 'handle'])->name('auth.resetpassword');
});

Route::middleware(['auth:api'])->group(function () {
    Route::post('logout', [UserController::class, 'logout'])->name('auth.logout');
    Route::get('user', [UserController::class, 'UserInfo'])->name('userInfo');
    Route::get('searchByEmail/{email}', [UserController::class, 'searchByEmail'])->name('searchByEmail');
    Route::prefix('classRoom')->group(function () {

        Route::prefix('schedule')->group(function () {
            Route::post('create', [ScheduleController::class, 'create'])->name('classRoom.schedule.create');
            Route::get('delete/{idClass}', [ScheduleController::class, 'delete'])->name('classRoom.schedule.create');
        });

        Route::prefix('exercise')->group(function () {
            Route::post('create', [ExamController::class, 'create'])->name('classRoom.exam.create');
            Route::get('show/{idExam}', [ExamController::class, 'show'])->name('classRoom.exam.show');
            Route::get('delete/{idExam}', [ExamController::class, 'delete'])->name('classRoom.exam.delete');
            Route::get('downloadFile/{filename}', [ExamController::class, 'downloadFile'])->name('classRoom.exam.downloadfile');
            Route::get('downloadFileExercise/{filename}', [ExamController::class, 'downloadFileExercise'])->name('classRoom.exam.downloadFileExercise');
            Route::get('listStudent/{idExam}', [ExamController::class, 'listStudentDoExercise'])->name('classRoom.exam.listStudentDoExercise');
            Route::post('returnExercise', [ExamController::class, 'returnExercise'])->name('classRoom.exam.returnExercise');
            Route::post('cancelSendExercise', [ExamController::class, 'cancelSendExercise'])->name('classRoom.exam.cancelExercise');
            Route::post('sendExercise', [ExamController::class, 'sendExercise'])->name('classRoom.exam.sendExercise');
            Route::post('file/delete', [ExamController::class, 'deletefile'])->name('classRoom.exam.deleteFile');
            Route::post('file/upload', [ExamController::class, 'uploadFile'])->name('classRoom.exam.uploadFile');
        });

        Route::get('list', [ClassRoomController::class, 'getList'])->name('classRoom.index');
        Route::get('join/{idClass}', [ClassRoomController::class, 'join'])->name('classRoom.join');
        Route::get('leave/{idClass}', [ClassRoomController::class, 'leave'])->name('classRoom.leave');
        Route::post('add', [ClassRoomController::class, 'add'])->name('classRoom.invite');
        Route::post('kick', [ClassRoomController::class, 'kick'])->name('classRoom.kick');
        Route::post('create', [ClassRoomController::class, 'create'])->name('classRoom.create');
        Route::get('delete/{idClass}', [ClassRoomController::class, 'delete'])->name('classRoom.delete');
        Route::get('show/{idClass}', [ClassRoomController::class, 'show'])->name('classRoom.show');
        //
    });
});
