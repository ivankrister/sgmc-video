<?php

use Illuminate\Support\Facades\Route;


use Illuminate\Support\Facades\Http;
use Illuminate\Support\Uri;

Route::view('stream', 'stream');

Route::get('test-url', function(){
  
    $m3u8Url  = "https://playmatic.live/sw.js";
    $referer = 'https://script.tangolinaction.com';

    $response = Http::withHeaders([
        'Referer' => $referer,
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3',
        
    ])->withOptions([
        'version' => 2.0, // Ensure HTTP/2 protocol
    ])->get($m3u8Url);
    return response($response->getProtocolVersion(), 200);


});

// Route for .m3u8 playlist
Route::get('/video/playlist.m3u8', function () {
    $m3u8Url = 'https://sgix02.tangolinaction.com/swift/live.m3u8';
    $referer = 'https://script.tangolinaction.com';

    // Fetch the m3u8 content
    //add bearer token Bearer BKzrgwjxpF7pwYYEh5IqisddOBWfUBDsloMZ1XHiUwOT02woPIVDg8OXgTs8mkrg
    $response = Http::withHeaders([
        'Referer' => $referer,
    ])->get($m3u8Url);

    // Check if the request was successful
    if ($response->successful()) {

        $response = response($response->body(), 200)
        ->header('Content-Type', 'application/vnd.apple.mpegurl')
        ->header('Cache-Control', 'public, max-age=10');

        //remove cookies
        $response->headers->remove('Set-Cookie');

        return $response;
    }




    return response()->json(['error' => 'Playlist not found'], 404);
});

// Route for .ts segments
Route::get('/video/{filename}.ts', function ($filename) {
    $url = 'https://sgix02.tangolinaction.com/swift/' . $filename . '.ts';
    $referer = 'https://script.tangolinaction.com';

    // Fetch the .ts segment
    $response = Http::withHeaders([
        'Referer' => $referer,
    ])->get($url);

    // Check if the request was successful
    if ($response->successful()) {
        return response($response->body(), 200)
            ->header('Content-Type', 'video/mp2t')
            ->header('Cache-Control', 'public, 86400');
    }

    return response()->json(['error' => 'Video segment not found'], 404);
});


Route::get('/', function () {
    return view('welcome');
});
