<?php

namespace App\Console\Commands\Finger;

use Illuminate\Console\Command;
use Rats\Zkteco\Lib\ZKTeco;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finger';

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
        $zk = new ZKTeco('192.168.1.201');
        $zk->connect();

        $name = $zk->getUser();
        dd($name);
    }
}
