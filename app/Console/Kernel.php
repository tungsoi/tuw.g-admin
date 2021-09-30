<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        'App\Console\Commands\System\CreateReportWarehousePortal',
        'App\Console\Commands\System\SubmitSuccessOrder',
        'App\Console\Commands\System\DeleteOrderDoesntHaveItem',
        'App\Console\Commands\Report\SaleRevenue'
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('report-warehouse:portal')->everyFiveMinutes();
        $schedule->command('purchase-order:delete-non-item')->everyMinute();
        $schedule->command('submit:success-order')->everyTenMinutes();

        $schedule->command('sale-report:update', ['2021-09-01', '2021-09-30'])->dailyAt("13:00");
        $schedule->command('sale-report:update', ['2021-09-01', '2021-09-30'])->dailyAt("21:00");

        $schedule->command('sale-report:update', ['2020-10-01', '2021-12-31'])->dailyAt("03:00");
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
