<?php

namespace App\Jobs;

use App\Models\System\TransactionWeight;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SubWalletWeightCustomer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $customer_id;
    protected $weight;
    protected $user_id_created;
    protected $content;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($customer_id, $weight, $user_id_created, $content)
    {
        $this->customer_id = $customer_id;
        $this->weight = $weight;
        $this->user_id_created = $user_id_created;
        $this->content = $content;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = User::find($this->customer_id);
        $user->wallet_weight -= $this->weight;
        $user->save();

        TransactionWeight::create([
            'customer_id'   =>  $this->customer_id,
            'user_id_created'   =>  $this->user_id_created,
            'content'   =>  $this->content,
            'kg'    =>  $this->weight
        ]);
    }
}
