<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('reservations:release-expired')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('bookings:release-expired')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();
