<?php

namespace App\Console\Commands\Notification;

use App\Jobs\Notification\TransportOrder;
use App\Jobs\Notification\PurchaseOrder;
use App\Jobs\Notification\Transaction;
use Illuminate\Console\Command;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification {type}';

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
        $user_id = 1378;

        switch ($this->argument('type')) {
            case 1:
                // transport_order
                dispatch(
                    new TransportOrder(
                        $user_id,
                        "Bạn có 1 đơn hàng mới chờ xuất kho - Mã đơn hàng C25856 Test. Bạn đến lấy hàng sớm nhé !",
                        25856
                    )
                );
                break;
            case 2:
                // purchase_order
                dispatch(
                    new PurchaseOrder(
                        $user_id,
                        "Bạn có 1 đơn hàng mới chờ xuất kho - Mã đơn hàng C25856 Test. Bạn đến lấy hàng sớm nhé !",
                        25856
                    )
                );
                break;
            case 3:
                // transaction
                dispatch(
                    new Transaction(
                        $user_id,
                        "Biến động số dư",
                        25856
                    )
                );
                break;
        }
    }
}
