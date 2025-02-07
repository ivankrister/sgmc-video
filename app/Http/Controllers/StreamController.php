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
        $m3u8Url = 'https://ac12.blodiab.com/sgmc/live.m3u8';

        $referer = 'https://blodiab.com/';

        return $this->getVideoPlaylist($m3u8Url, $referer, 2);

    }

    public function show($filename)
    {
        $url = 'https://ac12.blodiab.com/sgmc/'.$filename.'.ts';
        $referer = 'https://blodiab.com/';

        return $this->getVideoSegment($filename, $url, $referer);
    }

    public function streamIndex()
    {
        $m3u8Url = 'https://stm.pcl2023.live/api/video/playlist.m3u8';

        $referer = 'https://stm.pcl2023.live/';

        return $this->getVideoPlaylist($m3u8Url, $referer, 2);

    }

    public function streamShow($filename)
    {
        $url = 'https://stm.pcl2023.live/api/video/'.$filename.'.ts';
        $referer = 'https://stm.pcl2023.live/';

        return $this->getVideoSegment($filename, $url, $referer);
    }

    public function getVideoPlaylist($m3u8Url, $referer, $time)
    {
        if ($time <= 0) {

            $response = Http::withHeaders([
                'Accept' => '*/*',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Referer' => $referer,
                'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1',
            ])->withOptions([
                'version' => 2.0, // Use HTTP/2 for performance
            ])->get($m3u8Url);

            if ($response->successful()) {
                return response($response->body(), 200)
                    ->header('Cache-Control', 'public, max-age=1');
            }

            throw new \Exception("Failed to fetch playlist from URL: {$m3u8Url}");
        }
        $cacheKey = 'video_playlist_'.md5($m3u8Url);
        $lockKey = 'video_playlist_lock_'.md5($m3u8Url);

        return Cache::flexible($cacheKey, [$time, $time + 8], function () use ($m3u8Url, $referer) {
            $response = Http::withHeaders([
                'Accept' => '*/*',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Referer' => $referer,
                'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1',
            ])->withOptions([
                'version' => 2.0, // Use HTTP/2 for performance
            ])->get($m3u8Url);

            if ($response->successful()) {
                return response($response->body(), 200)
                    ->header('Cache-Control', 'public, max-age=1');
            }

            throw new \Exception("Failed to fetch playlist from URL: {$m3u8Url}");
        });

        // Check if a cached version exists
        if (Cache::has($cacheKey)) {
            return response(Cache::get($cacheKey), 200)
                ->header('Content-Type', 'text/plain; charset=utf-8')
                ->header('Cache-Control', 'public, max-age=1');
        }

        // Prevent multiple requests at the same time
        $lock = Cache::lock($lockKey, $time);

        if ($lock->get()) {
            try {
                $response = Http::withHeaders([
                    'Accept' => '*/*',
                    'Accept-Language' => 'en-US,en;q=0.9',
                    'Referer' => $referer,
                    'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1',
                ])->withOptions([
                    'version' => 2.0, // Use HTTP/2 for performance
                ])->get($m3u8Url);

                if ($response->successful()) {
                    $body = $response->body();

                    // Store the response in cache for 1 second
                    Cache::put($cacheKey, $body, now()->addSeconds($time));

                    return response($body, 200)
                        ->header('Content-Type', 'text/plain; charset=utf-8')
                        ->header('Cache-Control', 'public, max-age=1');
                }

                throw new \Exception("Failed to fetch playlist from URL: {$m3u8Url}");
            } catch (\Exception $e) {
                Log::error('M3U8 Playlist Fetch Error: '.$e->getMessage());

                return response()->json(['error' => 'Playlist not found'], 404);
            } finally {
                // Release the lock after processing
                $lock->release();
            }
        } else {
            // If another request is already processing, return the last known version (if available)
            return Cache::has($cacheKey)
                ? response(Cache::get($cacheKey), 200)->header('Content-Type', 'text/plain; charset=utf-8')->header('Cache-Control', 'public, max-age=1')
                : response()->json(['error' => 'Fetching playlist, try again'], 503);
        }
    }

    public function getVideoSegment($filename, $url, $referer)
    {
        $cacheKey = 'video_'.md5($filename);
        $lockKey = 'video_lock_'.md5($filename);

        // Serve cached response instantly if available
        if (Cache::has($cacheKey)) {
            return response(Cache::get($cacheKey), 200)
                ->header('Content-Type', 'application/octet-stream')
                ->header('Cache-Control', 'public, max-age=120');
        }

        // Prevent multiple requests at the same time
        $lock = Cache::lock($lockKey, 120); // Prevents multiple requests for 5 seconds

        if ($lock->get()) {
            try {
                $response = Http::withHeaders([
                    'Accept' => '*/*',
                    'Accept-Language' => 'en-US,en;q=0.9',
                    'Referer' => $referer,
                    'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/16.6 Mobile/15E148 Safari/604.1',
                ])->withOptions([
                    'version' => 2.0, // Use HTTP/2 for better performance
                ])->get($url);

                if ($response->successful()) {
                    $body = $response->body();

                    // Store in cache for 120 seconds
                    Cache::put($cacheKey, $body, now()->addSeconds(120));

                    return response($body, 200)
                        ->header('Accept-Ranges', 'bytes')
                        ->header('Access-Control-Allow-Credentials', 'true')
                        ->header('Access-Control-Allow-Headers', '*')
                        ->header('Access-Control-Allow-Methods', '*')
                        ->header('Access-Control-Allow-Origin', '*')
                        ->header('Access-Control-Expose-Headers', '*')
                        ->header('Cache-Control', 'public, max-age=120')
                        ->header('Content-Length', strlen($body))
                        ->header('Content-Type', 'application/octet-stream')
                        ->header('Last-Modified', gmdate('D, d M Y H:i:s').' GMT');
                }

                throw new \Exception("Failed to fetch video segment from URL: {$url}");
            } catch (\Exception $e) {
                Log::error('Video Segment Fetch Error: '.$e->getMessage());

                return response()->json(['error' => 'Video segment not found'], 404);
            } finally {
                // Release the lock after fetching the data
                $lock->release();
            }
        }

        // If another request is already fetching, return the last cached version (if available)
        return Cache::has($cacheKey)
            ? response(Cache::get($cacheKey), 200)->header('Content-Type', 'application/octet-stream')->header('Cache-Control', 'public, max-age=120')
            : response()->json(['error' => 'Fetching video segment, try again'], 503);
    }
}
