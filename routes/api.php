<?php

use App\Http\Controllers\Api\V1\AlbumController;
use App\Http\Controllers\Api\V1\ImageController;
use App\Http\Controllers\Api\V1\StrategyController;
use App\Http\Controllers\Api\V1\TokenController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Middleware\CheckIsEnableApi;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(CheckIsEnableApi::class)->group(function () {
    Route::get('strategies', [StrategyController::class, 'index'])->middleware('api.access:optional');
    Route::post('upload', [ImageController::class, 'upload'])->middleware('api.access:optional,images:write');
    Route::post('tokens', [TokenController::class, 'store'])->middleware('throttle:3,1');
    Route::get('images', [ImageController::class, 'images'])->middleware('api.access:required,images:read');
    Route::delete('images/{key}', [ImageController::class, 'destroy'])->middleware('api.access:required,images:delete');
    Route::get('albums', [AlbumController::class, 'index'])->middleware('api.access:required,albums:read');
    Route::delete('albums/{id}', [AlbumController::class, 'destroy'])->whereNumber('id')->middleware('api.access:required,albums:delete');
    Route::get('tokens', [TokenController::class, 'index'])->middleware('api.access:required,tokens:manage');
    Route::delete('tokens/{id}', [TokenController::class, 'destroy'])->middleware('api.access:required,tokens:manage')->whereNumber('id');
    Route::delete('tokens', [TokenController::class, 'clear'])->middleware('api.access:required,tokens:manage');
    Route::get('profile', [UserController::class, 'index'])->middleware('api.access:required,profile:read');
});
