<?php

namespace App\Console\Commands\System;

use App\Models\System\ScheduleLog;
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
        ini_set('memory_limit', '6400M');

        $users = User::select('id', 'wallet', 'symbol_name')
        ->whereIsCustomer(User::CUSTOMER)
        ->whereIsActive(User::ACTIVE)
        ->with('transactions')
        ->get();

        $begin = now();
        $err = [];
        foreach ($users as $key => $user) {
            echo ($key+1) . " - " . $user->symbol_name . " - wallet: " . $user->wallet . " - transaction: ";
            $user_wallet = number_format($user->wallet, 0, '.', '');
            $total = 0;
            if ($user->transactions->count() > 0) {

                foreach ($user->transactions as $transaction) {
                    if (in_array($transaction->type_recharge, [0, 1, 2])) {
                        $total += $transaction->money;
                    } else {
                        $total -= $transaction->money;
                    }
                }
                
                $total = number_format($total, 0, '.', '');
                echo $total . "\n";
        
                if ($total != $user_wallet) {
                    $err[] = $user->symbol_name;
                    $user->wallet = $total;
                    $user->save();
                }
            }

        }

        $end = now();

        $time = date('i:s', strtotime($begin )). " --> " . date('i:s', strtotime($end));
        
        ScheduleLog::create([
            'name'  =>  $this->signature . " - Time: " . $time . " - Error: ".sizeof($err)." - List: " . json_encode($err)
        ]);
    }
}
