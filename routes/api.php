<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;




Route::get('/video/playlist.m3u8', function () {

    $m3u8Url = 'https://sv11.turningpoint-v3.com:443/hls/stream/index.m3u8';

    $referer = 'https://sv1.turningpoint-v3.com/';
    $origin = 'https://sv1.turningpoint-v3.com';


    return Cache::store('octane')->remember('video_playlist', 1, function () use ($m3u8Url, $referer,$origin) {
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Accept-Language' => 'en-US,en;q=0.9',
            'Origin' => $origin,
            'Referer' => $referer,
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1',
        ])->withOptions([
            'version' => 2.0, // HTTP/2
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
                ->header('Cache-Control', 'public, max-age=3')
                ->header('Content-Length', strlen($response->body()))
                ->header('Content-Type', 'text/plain; charset=utf-8')
                ->header('Last-Modified', gmdate('D, d M Y H:i:s').' GMT');

            return $response;
        }

        return response()->json(['error' => 'Playlist not found'], 404);
    });

    return $response = Http::withHeaders([
            'Accept' => '*/*',
            'Accept-Language' => 'en-US,en;q=0.9',
            'Origin' => $origin,
            'Referer' => $referer,
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1',
        ])->withOptions([
            'version' => 2.0, // HTTP/2
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
                ->header('Cache-Control', 'public, max-age=3')
                ->header('Content-Length', strlen($response->body()))
                ->header('Content-Type', 'text/plain; charset=utf-8')
                ->header('Last-Modified', gmdate('D, d M Y H:i:s').' GMT');

            return $response;
        }

        return response()->json(['error' => 'Playlist not found'], 404);


});

Route::get('/video/{filename}.ts', function ($filename) {

    $url = 'https://sv11.turningpoint-v3.com:443/hls/stream/'.$filename.'.ts';
    $referer = 'https://sv1.turningpoint-v3.com/';
    $origin = 'https://sv1.turningpoint-v3.com';

    return Cache::remember('video_'.$filename, 120, function () use ($url, $referer,$origin) {
        $response = Http::withHeaders([
            'Accept' => '*/*',
            'Accept-Language' => 'en-US,en;q=0.9',
            'Origin' => $origin,
            'Referer' => $referer,
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1',
        ])->withOptions([
            'version' => 2.0, // HTTP/2
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
                ->header('Last-Modified', gmdate('D, d M Y H:i:s').' GMT');
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
