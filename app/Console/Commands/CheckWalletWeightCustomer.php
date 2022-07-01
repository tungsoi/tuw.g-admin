<?php

namespace App\Console\Commands;

use App\Models\System\TransactionWeight;
use App\User;
use Illuminate\Console\Command;

class CheckWalletWeightCustomer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check_wallet_weight:customer';

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
        $add = TransactionWeight::whereType(2)->get();

        $temp = [];
        foreach ($add as $row) {
            if (isset($temp[$row->customer_id])) {
                $temp[$row->customer_id] += (int) $row->kg;
            } else {
                $temp[$row->customer_id] = (int) $row->kg;
            }
        }

        $res = [];

        foreach ($temp as $user_id => $kg_add) {
            // if ($user_id == 432) {
            //     dd( (float) $kg_add);
            // }
            $kg_add = (float) $kg_add;
            $wallet = (float) User::find($user_id)->wallet_weight;
            $used = TransactionWeight::whereType(1)->whereCustomerId($user_id)->sum('kg');
            $owed = $kg_add - $used;

            if ($wallet != $owed) {
                $res[] = [
                    'user_id'   =>  $user_id,
                    'add'   =>  $kg_add,
                    'wallet'    =>  $wallet,
                    'used'  =>  $used,
                    'conflict'  =>  ($owed) . " - " . $wallet
                ];
            }
        }

        dd($res);
        dd($add->count());
    }
}
