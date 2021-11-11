<?php

namespace App\Console\Commands\FingerPrint;

use App\Console\Commands\FingerPrint\Lib\ZkLib;
use Illuminate\Console\Command;

class Attendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:attendance';

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

        set_time_limit(0);
        ini_set("max_execution_time", 0);
        ini_set("memory_limit", "10240M");
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
       
        $zklib = new ZkLib("192.168.1.201", 4370);
        // dd($zklib);
        $serial_number = $zklib->getDeviceName();

        dd($serial_number);
    }
}
