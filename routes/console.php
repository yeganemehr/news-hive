<?php

use App\Console\Commands\Fetch;
use Illuminate\Support\Facades\Schedule;

// Guardian: 500 calls per day.
Schedule::command(Fetch::class, ['--source=guardian'])
    ->withoutOverlapping()
    ->everyFifteenMinutes()
    ->onOneServer();

// NYTimes: 500 requests per day and 5 requests per minute
Schedule::command(Fetch::class, ['--source=nytimes'])
    ->withoutOverlapping()
    ->everyFifteenMinutes()
    ->onOneServer();

// ESPN: Unknown limit
Schedule::command(Fetch::class, ['--source=espn'])
    ->withoutOverlapping()
    ->everyFifteenMinutes()
    ->onOneServer();
