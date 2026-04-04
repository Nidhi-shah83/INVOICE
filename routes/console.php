<?php

use App\Console\Commands\CheckNotifications;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:check-notifications', function () {
    app(CheckNotifications::class)->handle();
})->purpose('Generate collection and invoice notifications for authenticated users');
