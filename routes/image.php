<?php

use App\Http\Controllers\MediaController;
use Illuminate\Support\Facades\Route;

$imageTypes = 'jpg|jpeg|png|gif|tif|tiff|bmp|ico|psd|webp|heic|svg';

Route::get('media/review/{token}', [MediaController::class, 'review'])
    ->where('token', '[a-f0-9]{32}')->name('media.review');
Route::get('media/{key}/thumbnail.webp', [MediaController::class, 'thumbnail'])
    ->whereAlphaNumeric('key')->name('media.thumbnail');
Route::get('{key}.{extension}', [MediaController::class, 'original'])
    ->where('key', '[a-zA-Z0-9]+')
    ->where('extension', '(?i:'.$imageTypes.')')->name('media.original');
