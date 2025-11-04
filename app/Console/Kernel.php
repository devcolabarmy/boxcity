<?php

namespace App\Console;

use App\Jobs\FetchProductsFromApi;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            (new FetchProductsFromApi)->handle();
        })
        ->everyMinute()
        ->withoutOverlapping(5);
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
