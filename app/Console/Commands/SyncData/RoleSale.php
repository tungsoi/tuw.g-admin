<?php

namespace App\Console\Commands\SyncData;

use App\Models\Setting\RoleUser;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RoleSale extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:role-sale';

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
        $ids = ["423", "503", "1034", "1190", "1253", "1311", "1372", "1418", "1419", "1420", "1423", "1477", "1864", "1881", "1927", "1928", "1929", "1931", "2098", "2103", "2156", "2236", "2373", "2431", "2434", "2455", "2467", "2492", "2578", "2603", "2728", "2763", "2814", "2848", "2932", "2955", "3042", "3102", "3158", "3256", "3711"];
        $users = User::select('id', 'is_active')->whereIn('id', $ids)->whereIsActive(1)->get();

        foreach ($users as $user) {
            RoleUser::create([
                'user_id'   =>  $user->id,
                'role_id'   =>  3
            ]);
        }
    }
}