<?php

namespace App\Jobs;

use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\System\Transaction;
use App\Models\System\TransactionType;
use App\User;
use Encore\Admin\Facades\Admin;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class HandleSubmitSuccessOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $orderId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $order = PurchaseOrder::where('status', '!=', 9)->where('id', $this->orderId)->first();

        if ($order && $order->status != 9) {
            
        // step 1: change status of order
            $order->status = 9;
            $order->success_at = now();
            $order->user_success_at = 1;
            $order->save();

            // step 2: calculator money
            $deposited = (int) $order->deposited;
            $amount_rmb = str_replace(",", "", $order->amount());
            $amount_vnd = (int) number_format( $amount_rmb * $order->current_rate, 0, '.', '');
            $owed = number_format( (int) ($amount_vnd - $deposited) , 0, '.', '');

            if ($owed > 0) {
                $type = 3; // tru tien
                $content = "Thanh toán đơn hàng mua hộ. Mã đơn hàng ".$order->order_number;
            } else {
                $type = 2; // hoan tien
                $content = "Thanh toán đơn hàng mua hộ. Mã đơn hàng ".$order->order_number.". (Dư tiền cọc).";
                $owed = abs($owed);
            }

            $flag = Transaction::where('content', 'like', '%'.$content.'%')->count();

            if ($flag == 0) {
                $customer = User::find($order->customer_id);
                $transactionType = TransactionType::find($type);

                if ($transactionType->type == 'add') {
                    // cộng tiền
                    $customer->wallet += $owed;
                    $customer->save();
                } else {
                    // trừ tiền
                    $customer->wallet -= $owed;
                    $customer->save();
                }

                // create transaction
                Transaction::create([
                    'customer_id'   =>  $order->customer_id,
                    'user_id_created'   =>  1,
                    'type_recharge' =>  $type,
                    'content'   =>  $content,
                    'money'     =>  $owed,
                    'order_id'  =>  $order->id
                ]);
            }
        }
        return true;
    }
}
