<?php

namespace App\Console\Commands\SyncData;

use App\Models\SyncData\AlilogiTransportOrder;
use Illuminate\Console\Command;

class PaymentOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:payment-order';

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
        $transportOrders = AlilogiTransportOrder::all();

        foreach ($transportOrders as $order) {
            dd($order);
        }

    }
}
