<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware('shopify.host')->group(function() {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.home');
    Route::prefix('auth')->name('shopify.')->group(function() {
        Route::get('install', [AuthController::class, 'install'])->name('shopify.install');
        Route::get('callback', [AuthController::class, 'callback'])->name('callback');
    });
});

Route::get('app/settings', [DashboardController::class, 'index']);
Route::get('app/videos', [DashboardController::class, 'index']);
Route::get('app/', [DashboardController::class, 'index']);
