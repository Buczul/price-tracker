<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Codzienne sprawdzanie cen w sklepach o godzinie 02:00 w nocy
Schedule::command('prices:check')->dailyAt('02:00');