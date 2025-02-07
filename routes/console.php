<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    DB::table('cache')
        ->where('expiration', '<', now()->timestamp)
        ->delete();
})->hourly();
