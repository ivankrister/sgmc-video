<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

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
            ->header('Accept-Ranges', 'bytes')
            ->header('Access-Control-Allow-Credentials', 'true')
            ->header('Access-Control-Allow-Headers', '*')
            ->header('Access-Control-Allow-Methods', '*')
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Expose-Headers', '*')
            ->header('Cache-Control', 'public, max-age=1')
            ->header('Content-Length', strlen($response->body()))
            ->header('Content-Type', 'text/plain; charset=utf-8')
            ->header('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');

        return $response;
    }




    return response()->json(['error' => 'Playlist not found'], 404);
});


Route::get('/video/{filename}.ts', function ($filename) {
    $url = 'https://sgix02.tangolinaction.com/swift/' . $filename . '.ts';
    $referer = 'https://script.tangolinaction.com';


    //Cache remember for 30seconds to avoid multiple request

    return Cache::remember('video_' . $filename, 30, function () use ($url, $referer) {
        // Fetch the .ts segment
        $response = Http::withHeaders([
            'Referer' => $referer,
        ])->get($url);

        // Check if the request was successful
        if ($response->successful()) {
            return response($response->body(), 200)
                ->header('Accept-Ranges', 'bytes')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Allow-Headers', '*')
                ->header('Access-Control-Allow-Methods', '*')
                ->header('Access-Control-Allow-Origin', '*')
                ->header('Access-Control-Expose-Headers', '*')
                ->header('Cache-Control', 'public, max-age=600')
                ->header('Content-Length', strlen($response->body()))
                ->header('Content-Type', 'application/octet-stream')
                ->header('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');
        }
        return response()->json(['error' => 'Video segment not found'], 404);
    });

    // Fetch the .ts segment
    // $response = Http::withHeaders([
    //     'Referer' => $referer,
    // ])->get($url);

    // // Check if the request was successful
    // if ($response->successful()) {
    //     return response($response->body(), 200)
    //         ->header('Accept-Ranges', 'bytes')
    //         ->header('Access-Control-Allow-Credentials', 'true')
    //         ->header('Access-Control-Allow-Headers', '*')
    //         ->header('Access-Control-Allow-Methods', '*')
    //         ->header('Access-Control-Allow-Origin', '*')
    //         ->header('Access-Control-Expose-Headers', '*')
    //         ->header('Cache-Control', 'public, max-age=600')
    //         ->header('Content-Length', strlen($response->body()))
    //         ->header('Content-Type', 'application/octet-stream')
    //         ->header('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');
    // }
    // return response()->json(['error' => 'Video segment not found'], 404);
});