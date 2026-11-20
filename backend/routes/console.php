<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Used on a SaaS host where cron calls `php artisan schedule:run` every minute.
// The GitHub Actions workflow remains the scheduler for the free local setup.
Schedule::command('jobhunter:run')
    ->everyThirtyMinutes()
    ->withoutOverlapping();
