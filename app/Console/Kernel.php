<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\parametre;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            try {
                $actif = parametre::getValue('cron-alerte_compte_debiteur_actif', false);
                $jour  = parametre::getValue('cron-alerte_compte_debiteur_jour', 1);

                if ($actif && (int) date('j') === (int) $jour) {
                    \Artisan::call('sendAccountAlert', ['--force' => true]);
                }
            } catch (\Exception $e) {
                \Log::error('Cron alerte compte débiteur : ' . $e->getMessage());
            }
        })->dailyAt('08:00');

        $schedule->command('helloasso:retry-payments')->everyFiveMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
