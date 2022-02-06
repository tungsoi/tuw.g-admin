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
        'App\Console\Commands\System\DeleteErrorTransportCodeChina',
        'App\Console\Commands\System\FillExportAtPaymentOrder',
        'App\Console\Commands\Report\SaleRevenue',
        'App\Console\Commands\Report\SaleSalary',
        'App\Console\Commands\Report\OrderReport',
        'App\Console\Commands\System\DeleteNullItemPaymentOrder',
        'App\Console\Commands\System\SyncWalletCustomer',
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
        $schedule->command('submit:success-order')->everyMinute();
        $schedule->command('delete:error-transport-code-china')->everyMinute();
        $schedule->command('payment_order:fill_export_at')->hourly();
        $schedule->command('delete:null-payment-order')->hourly();
        $schedule->command('sync:customer-wallet')->everyMinute();
        $schedule->command('order:report', [1])->dailyAt("13:00");
        $schedule->command('order:report', [1])->dailyAt("19:00");
        $schedule->command('order:report', [1])->dailyAt("23:59");
        $schedule->command('order:report', [2])->dailyAt("13:00");
        $schedule->command('order:report', [2])->dailyAt("19:00");
        $schedule->command('order:report', [2])->dailyAt("23:59");

        $schedule->command('sale-revenue-report:update', ['2022-02-01', '2022-02-28'])->dailyAt("13:00");
        $schedule->command('sale-revenue-report:update', ['2022-02-01', '2022-02-28'])->dailyAt("21:00");
        $schedule->command('sale:salary', ['2022-02-01', '2022-02-28'])->hourly();

        // $schedule->command('sale-revenue-report:update', ['2020-10-01', '2022-01-31'])->dailyAt("03:00");
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
