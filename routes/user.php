<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group([], static function () {
    Route::group(['middleware' => 'superadmin'], static function () {
        Route::get('/users', [App\Http\Controllers\Admin\User\UserController::class, 'index'])->name('user.index');
        Route::get('/user/verification/status', [App\Http\Controllers\Admin\User\UserController::class, 'getUserVerificationStatus'])->name('user.verification.status');
        Route::get('/users/page/{page}', [App\Http\Controllers\Admin\User\UserController::class, 'getPaginatedUsers'])->name('user.list');
        Route::get('/users/download', [App\Http\Controllers\Admin\User\UserController::class, 'downloadUsers'])->name('user.download');
    });

    Route::get('/profile', [App\Http\Controllers\Admin\User\UserController::class, 'showUserProfile'])->name('user.profile');
});
