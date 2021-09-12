<?php

namespace App\Console\Commands\SyncData;

use App\Models\Setting\RoleUser;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RoleOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:role-order';

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
        $ids = ["1427", "1544", "1716", "1866", "1867", "3585"];
        $users = User::select('id', 'is_active')->whereIn('id', $ids)->whereIsActive(1)->get();

        foreach ($users as $user) {
            RoleUser::create([
                'user_id'   =>  $user->id,
                'role_id'   =>  4
            ]);
        }
    }
}