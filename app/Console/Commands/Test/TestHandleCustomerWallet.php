<?php

namespace App\Console\Commands\Test;

use App\Jobs\HandleCustomerWallet;
use App\Models\System\TransactionWeight;
use App\User;
use Illuminate\Console\Command;

class TestHandleCustomerWallet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:job-handle-customer-wallet';

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
        // nhan
        $user_ids = TransactionWeight::whereType(2)->get()->pluck('customer_id')->toArray();
        $user_ids = array_unique($user_ids);

        foreach ($user_ids as $user_id) {
            $user = User::find($user_id);
            $rev = TransactionWeight::whereType(2)->whereCustomerId($user_id)->sum('kg');
            $rev = number_format($rev, 2, '.', '');
            $used = TransactionWeight::whereType(1)->whereCustomerId($user_id)->sum('kg');
            $used = number_format($used, 2, '.', '');
            $owed = $rev-$used;
            $wallet = $user->wallet_weight;

            if ($owed < 0) {
                echo $user->symbol_name . " - Duoc nhan: "
                    . $rev
                    . " - Da dung: "
                    . $used
                    . " - Con du: " . $owed
                    . " - Trong vi: " . $wallet
                    . "\n";
            }
        }
    }
}
