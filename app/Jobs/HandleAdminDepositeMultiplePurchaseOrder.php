<?php

namespace App\Jobs;

use App\Admin\Services\OrderService;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\System\Transaction;
use App\Models\System\TransactionType;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class HandleAdminDepositeMultiplePurchaseOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order_id;
    protected $percent;
    protected $user_created_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($order_id, $percent, $user_created_id)
    {
        $this->order_id = $order_id;
        $this->percent = $percent;
        $this->user_created_id = $user_created_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $orderService = new OrderService();

        $order = PurchaseOrder::find($this->order_id);

        if ($order->status == $orderService->getStatus('new-order')) {

            // amount item price
            $totalItemPrice = str_replace(',', '', $order->sumItemPrice());

            $percent = (int) $this->percent;
            $depositedRmb = $totalItemPrice / 100 * $percent;
            $depositedVnd = $depositedRmb * $order->current_rate;
            $deposited = number_format($depositedVnd, 0, '.', '');

            $order->status = $orderService->getStatus('deposited');
            $order->deposited = $deposited;
            $order->deposited_at = now();
            $order->user_deposited_at = $this->user_created_id;
            $order->save();

            $job = new HandleCustomerWallet(
                $order->customer->id,
                $this->user_created_id, // admin
                $deposited,
                3,
                "Đặt cọc đơn hàng mua hộ $order->order_number"
            );
            dispatch($job);
        }

        return true;
    }
}
