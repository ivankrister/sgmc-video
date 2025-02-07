<?php

use App\Http\Controllers\StreamController;
use Illuminate\Support\Facades\Route;

Route::get('/video/playlist.m3u8', [StreamController::class, 'index']);

Route::get('/video/{filename}.ts', [StreamController::class, 'show']);

Route::get('/stream/playlist.m3u8', [StreamController::class, 'streamIndex']);

Route::get('/stream/{filename}.ts', [StreamController::class, 'streamShow']);
