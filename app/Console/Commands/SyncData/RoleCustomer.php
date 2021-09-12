<?php

namespace App\Console\Commands\SyncData;

use App\Models\Setting\RoleUser;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RoleCustomer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:role-customer';

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
        // dong bo du lieu users
        // step 1: copy raw tu database alilogi
        // step 2: check lai ma khach hang null
        // step 3: check ma khach hang trung nhau

        $users = User::select('id', 'is_customer', 'symbol_name')->whereIsCustomer(1)->get();

        foreach ($users as $user) {
            echo $user->symbol_name . "\n";

            $data = [
                'role_id'   =>  2,
                'user_id'   =>  $user->id
            ];

            RoleUser::create($data);
        }
    }
}
