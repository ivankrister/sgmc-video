<?php

use Illuminate\Support\Facades\Route;


use Illuminate\Support\Facades\Http;


// Route for .m3u8 playlist
Route::get('/video/playlist.m3u8', function () {
    $m3u8Url = 'https://sgix02.tangolinaction.com/hls/server2/index.m3u8';
    $referer = 'https://script.tangolinaction.com';

    // Fetch the m3u8 content
    //add bearer token Bearer BKzrgwjxpF7pwYYEh5IqisddOBWfUBDsloMZ1XHiUwOT02woPIVDg8OXgTs8mkrg
    $response = Http::withHeaders([
        'Referer' => $referer,
    ])->get($m3u8Url);

    // Check if the request was successful
    if ($response->successful()) {
        return response($response->body(), 200)
            ->header('Content-Type', 'application/vnd.apple.mpegurl')
            ->header('Cache-Control', 'no-cache');
    }

    dd($response->body());



    return response()->json(['error' => 'Playlist not found'], 404);
});

// Route for .ts segments
Route::get('/video/{filename}.ts', function ($filename) {
    $url = 'https://sgix02.tangolinaction.com/hls/server2/' . $filename . '.ts';
    $referer = 'https://script.tangolinaction.com';

    // Fetch the .ts segment
    $response = Http::withHeaders([
        'Referer' => $referer,
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
