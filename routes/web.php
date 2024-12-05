<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;


Route::view('stream', 'stream');
// Route for .m3u8 playlist
Route::get('/video/playlist.m3u8', function () {
    $m3u8Url = 'https://ac01.blodiab.com/sgmc/live.m3u8';
    $referer = 'https://blodiab.com/';
    $clientIp = '180.191.117.73'; // Simulated client IP

    // Fetch the m3u8 content with the simulated client IP
    $response = Http::withHeaders([
        'Referer' => $referer,
        'X-Forwarded-For' => $clientIp, // Simulating client IP
    ])->get($m3u8Url);

    // Check if the request was successful
    if ($response->successful()) {
        return response($response->body(), 200)
            ->header('Content-Type', 'application/vnd.apple.mpegurl')
            ->header('Cache-Control', 'no-cache');
    }

    return response()->json(['error' => 'Playlist not found'], 404);
});

// Route for .ts segments
Route::get('/video/{filename}.ts', function ($filename) {
    $url = 'https://ac01.blodiab.com/sgmc/' . $filename . '.ts';
    $referer = 'https://blodiab.com/';
    $clientIp = '180.191.117.73'; // Simulated client IP

    // Fetch the .ts segment with the simulated client IP
    $response = Http::withHeaders([
        'Referer' => $referer,
        'X-Forwarded-For' => $clientIp, // Simulating client IP
    ])->get($url);

    // Check if the request was successful
    if ($response->successful()) {
        return response($response->body(), 200)
            ->header('Content-Type', 'video/mp2t')
            ->header('Cache-Control', 'no-cache');
    }

    return response()->json(['error' => 'Video segment not found'], 404);
});


Route::get('/', function () {
    return view('welcome');
});
