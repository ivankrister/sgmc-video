<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
class StreamController extends Controller
{
    
    public function index()
    {
        $m3u8Url = 'https://sv11.turningpoint-v3.com:443/hls/stream/index.m3u8';

        $referer = 'https://sv1.turningpoint-v3.com/';
        $origin = 'https://sv1.turningpoint-v3.com';

        return $this->getVideoPlaylist($m3u8Url, $referer, $origin);

    }

public function getVideoPlaylist($m3u8Url, $referer, $origin)
{
    $cacheKey = 'video_playlist_' . md5($m3u8Url);
    $lockKey = 'video_playlist_lock_' . md5($m3u8Url);

    // Check if a cached version exists
    if (Cache::store('octane')->has($cacheKey)) {
        return response(Cache::get($cacheKey), 200)
            ->header('Content-Type', 'text/plain; charset=utf-8')
            ->header('Cache-Control', 'public, max-age=1');
    }

    // Prevent multiple requests at the same time
    $lock = Cache::lock($lockKey, 2);

    if ($lock->get()) {
        try {
            $response = Http::withHeaders([
                'Accept'             => '*/*',
                'Accept-Language'    => 'en-US,en;q=0.9',
                'Origin'             => $origin,
                'Referer'            => $referer,
                'User-Agent'         => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1',
            ])->withOptions([
                'version' => 2.0, // Use HTTP/2 for performance
            ])->get($m3u8Url);

            if ($response->successful()) {
                $body = $response->body();

                Cache::store('octane')->put($cacheKey, $body, 2);

                return response($body, 200)
                    ->header('Content-Type', 'text/plain; charset=utf-8')
                    ->header('Cache-Control', 'public, max-age=1');
            }

            throw new \Exception("Failed to fetch playlist from URL: {$m3u8Url}");
        } catch (\Exception $e) {
            Log::error("M3U8 Playlist Fetch Error: " . $e->getMessage());
            return response()->json(['error' => 'Playlist not found'], 404);
        } finally {
            // Release the lock after processing
            $lock->release();
        }
    } else {
        // If another request is already processing, return the last known version (if available)
        return Cache::store('octane')->has($cacheKey)
            ? response(Cache::store('octane')->get($cacheKey), 200)->header('Content-Type', 'text/plain; charset=utf-8')->header('Cache-Control', 'public, max-age=1')
            : response()->json(['error' => 'Fetching playlist, try again'], 503);
    }
}

}
