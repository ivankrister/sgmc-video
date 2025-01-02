<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/video/playlist.m3u8', function () {
    // $m3u8Url = 'https://sgix02.tangolinaction.com/swift/live.m3u8';
    // $referer = 'https://script.tangolinaction.com';

    $m3u8Url = 'https://media2.antmedia.site/revi/stream.m3u8';
    $referer = 'https://antmedia.site/';

   



  
    
    


    
   return Cache::flexible('playlist', [3, 6], function () use($m3u8Url, $referer) {
    
    $response = Http::withHeaders([
        'Referer' => $referer,
        'User-Agent' => 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/26.0 Chrome/122.0.0.0 Mobile Safari/537.36',
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
            ->header('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');

        return $response;
    }




    return response()->json(['error' => 'Playlist not found'], 404);

    });


   
});


Route::get('/video/{filename}.ts', function ($filename) {
    // $url = 'https://sgix02.tangolinaction.com/swift/' . $filename . '.ts';
    // $referer = 'https://script.tangolinaction.com';


    $url = 'https://media2.antmedia.site/revi/' . $filename . '.ts';
    $referer = 'https://antmedia.site/';
 


    //Cache remember for 30seconds to avoid multiple request

    return Cache::remember('video_' . $filename, 120, function () use ($url, $referer) {
        // Fetch the .ts segment
        $response = Http::withHeaders([
            'Referer' => $referer,
            'User-Agent' => 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/26.0 Chrome/122.0.0.0 Mobile Safari/537.36',
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
