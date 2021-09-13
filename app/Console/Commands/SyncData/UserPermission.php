<?php

namespace App\Console\Commands\SyncData;

use App\Models\Setting\RoleUser;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UserPermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:user-permission';

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
        $users = User::whereIsActive(User::ACTIVE)->get();

        foreach ($users as $user) {
            DB::table('admin_user_permissions')->insert([
                'permission_id' =>  2,
                'user_id'   =>  $user->id
            ]);
        }
    }
}