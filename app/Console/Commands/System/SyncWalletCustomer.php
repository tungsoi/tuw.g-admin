<?php

namespace App\Console\Commands\System;

use App\User;
use Illuminate\Console\Command;
use App\Models\System\Transaction as SystemTransaction;

class SyncWalletCustomer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:customer-wallet';

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
        $users = User::select('id', 'wallet', 'symbol_name')->whereIsCustomer(User::CUSTOMER)->whereIsActive(User::ACTIVE)->get();

        foreach ($users as $user) {
            echo $user->symbol_name . "\n";
            $user_wallet = number_format($user->wallet, 0, '.', '');
            $transactions = SystemTransaction::select('money', 'type_recharge')->where('money', ">", 0)
            ->where('customer_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

            $total = 0;

            if ($transactions->count() > 0) {

                foreach ($transactions as $transaction) {
                    if (in_array($transaction->type_recharge, [0, 1, 2])) {
                        $total += $transaction->money;
                    } else {
                        $total -= $transaction->money;
                    }
                }
        
                $total = number_format($total, 0, '.', '');
        
                if ($total != $user_wallet) {
                    dd($user->id);
                }
            }
        }
    }
}
