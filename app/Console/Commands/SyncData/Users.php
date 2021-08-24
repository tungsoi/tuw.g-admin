<?php

namespace App\Console\Commands\SyncData;

use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Users extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:users';

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

        $users = User::all();

        foreach ($users as $user) {
            echo $user->symbol_name . " / ";
            if ($user->symbol_name == "") {
                $user->symbol_name = "MKH".str_pad($user->id, 5, 0, STR_PAD_LEFT);
                $user->save();

                echo " null \n";
            } else {
                $flag = User::whereSymbolName($user->symbol_name)->orderBy('id', 'asc')->get();

                if ($flag->count() > 1) {
                    echo " duplicate \n";
                    foreach ($flag as $key => $value) {
                        if ($key != 0) {
                            $value->symbol_name .= "-".($key+1);
                            $value->save();
                        }
                    }
                } else {
                    echo " normal \n";
                }
            }
        }
        
    }
}
