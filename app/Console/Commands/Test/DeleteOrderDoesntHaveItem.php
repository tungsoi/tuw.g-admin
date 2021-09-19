<?php

namespace App\Console\Commands\Test;

use App\Models\PurchaseOrder\PurchaseOrder;
use Illuminate\Console\Command;

class DeleteOrderDoesntHaveItem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:delete-non-item';

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
        $time = date('Y-m-d', strtotime(now()));
        PurchaseOrder::select('id')
            ->whereIn('status', [2, 10])
            ->where('created_at', 'like', $time.'%')
            ->doesntHave('items')
            ->delete();
    }
}
