<?php

use App\Http\Controllers\StreamController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/video/playlist.m3u8', [StreamController::class, 'index']);

Route::get('/video/{filename}.ts', [StreamController::class, 'show']);