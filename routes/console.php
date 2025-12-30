<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Dijalankan setiap hari pukul 00:01 untuk mengunci tahap sebelumnya
Schedule::command('ami:sync-stages')->dailyAt('00:01');
