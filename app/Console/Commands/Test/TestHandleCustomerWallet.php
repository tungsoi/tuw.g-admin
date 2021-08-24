<?php

namespace App\Console\Commands\Test;

use App\Jobs\HandleCustomerWallet;
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
        $job = new HandleCustomerWallet(
            684,
            1,
            rand(100000, 1000000),
            0,
            "Test Nạp tiền chuyển khoản"
        );
        dispatch($job);
        
        $job = new HandleCustomerWallet(
            684,
            1,
            rand(100000, 1000000),
            1,
            "Test Nạp tiền mặt"
        );
        dispatch($job);

        $job = new HandleCustomerWallet(
            684,
            1,
            rand(100000, 1000000),
            2,
            "Test Hoàn tiền"
        );
        dispatch($job);

        $job = new HandleCustomerWallet(
            684,
            1,
            rand(100000, 1000000),
            3,
            "Test Trừ tiền"
        );
        dispatch($job);

    }
}
