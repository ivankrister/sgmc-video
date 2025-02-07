<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('cache:delete-expired')->hourly();
