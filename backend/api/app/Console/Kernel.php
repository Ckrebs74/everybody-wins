<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Startet geplante Raffles jede Minute
        $schedule->command('raffles:start')
                 ->everyMinute()
                 ->withoutOverlapping()
                 ->runInBackground();

        // Prüft ob Raffles bereit zur Ziehung sind (jede Minute)
        $schedule->command('raffles:check-progress')
                 ->everyMinute()
                 ->withoutOverlapping()
                 ->runInBackground();

        // Zieht automatisch Raffles die "pending_draw" sind
        // WICHTIG: Nur für normale tägliche Raffles, nicht für Highlights!
        // Highlights werden manuell im Livestream gezogen
        $schedule->command('raffles:draw --auto')
                 ->everyFiveMinutes()
                 ->withoutOverlapping()
                 ->runInBackground();

        // Optional: Cleanup alte Sessions/Cache
        $schedule->command('cache:prune-stale-tags')
                 ->hourly();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}