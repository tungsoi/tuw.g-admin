<?php

namespace App\Console\Commands\Notification;

use App\Admin\Services\NotificationService;
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

        $service = new NotificationService();

        switch ($this->argument('type')) {
            case 1:
                // transport_order
                $service->sendTransportOrder($user_id, 25856);
                break;
            case 2:
                // purchase_order
                $service->sendPurchaseOrder($user_id, 42791);
                break;
            case 3:
                // transaction
                $service->sendTransaction($user_id, "Biến động số dư: - 1,500,000 VND. Thanh toán đơn hàng mua hộ EC-012322");
                break;
        }
    }
}
