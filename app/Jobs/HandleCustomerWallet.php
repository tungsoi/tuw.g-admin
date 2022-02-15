<?php

namespace App\Jobs;

use App\Models\System\Transaction;
use App\Models\System\TransactionType;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class HandleCustomerWallet implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $customerId;
    protected $userCreatedId;
    protected $money;
    protected $type;
    protected $content;
    protected $order_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($customerId, $userCreatedId = 1, $money = 0, $type, $content = "", $order_id = "")
    {
        $this->customerId = $customerId;
        $this->userCreatedId = $userCreatedId;
        $this->money = $money;
        $this->type = $type;
        $this->content = $content;
        $this->order_id = $order_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
            $flag = Transaction::where('content', $this->content)->get();

            if ($flag->count() == 0) {
                // update wallet
                $customer = User::find($this->customerId);
                $transactionType = TransactionType::find($this->type);

                if ($transactionType->type == 'add') {
                    // cộng tiền
                    $customer->wallet += $this->money;
                    $customer->save();
                } else {
                    // trừ tiền
                    $customer->wallet -= $this->money;
                    $customer->save();
                }

                // create transaction
                Transaction::create([
                    'customer_id'   =>  $this->customerId,
                    'user_id_created'   =>  $this->userCreatedId,
                    'type_recharge' =>  $this->type,
                    'content'   =>  $this->content,
                    'money'     =>  $this->money,
                    'order_id'  =>  $this->order_id
                ]);

                return true;
            }
    }
}
