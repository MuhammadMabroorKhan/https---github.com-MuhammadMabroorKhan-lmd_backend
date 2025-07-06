<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('lmd:process-earnings')->everyMinute();



//  Every 1 Hour and 15 Minutes
// Laravel doesn’t directly support custom intervals like "1 hour 15 min", but you can use:
// Schedule::command('your:command')->cron('*/75 * * * *');
// 🔍 Explanation: */75 = every 75 minutes

// After 1 Day (i.e. every 24 hours)
// Schedule::command('your:command')->daily();
// If you want to run once a day, this is the best.


//  At Specific Time Daily (e.g., every day at 8:15 AM)
// Schedule::command('your:command')->dailyAt('08:15');
// You can also use ->timezone('Asia/Karachi') if needed.


// Every Monday at 3 PM
// Schedule::command('your:command')->weeklyOn(1, '15:00');
// 1 is Monday (0 = Sunday, 1 = Monday, ..., 6 = Saturday)

// Every 3 Days
// Laravel doesn’t have built-in everyThreeDays(), so use a custom cron expression:
// Schedule::command('your:command')->cron('0 0 */3 * *');
// This runs at midnight every 3rd day





Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();
