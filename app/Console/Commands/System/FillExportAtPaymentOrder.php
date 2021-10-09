<?php

namespace App\Console\Commands\System;

use App\Models\PaymentOrder\PaymentOrder;
use App\Models\System\ScheduleLog;
use Illuminate\Console\Command;

class FillExportAtPaymentOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment_order:fill_export_at';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $orders = PaymentOrder::where('status', 'payment_export')->whereNull('export_at')->get();
        echo "Total: ".$orders->count() . "\n";
        foreach ($orders as $order) {
            echo $order->order_number . "\n";
            $time = $order->updated_at;
            $order->export_at = $time;
            $order->save();

            $order->updated_at = $time;
            $order->save();
        }

        ScheduleLog::create([
            'name'  =>  $this->signature . " - " . $orders->count()
        ]);
    }
}
