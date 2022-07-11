<?php

namespace App\Console\Commands\System;

use App\Models\PurchaseCustomerReport;
use App\Models\PurchaseCustomerReportDetail;
use App\User;
use Illuminate\Console\Command;

class RunPurchaseCustomerReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:purchase_customer_order {begin} {finish}';

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
        ini_set("memory_limit","256M");

        $begin = $this->argument('begin')." 00:00:01";
        $finish = $this->argument('finish')." 23:59:59";

        $report = PurchaseCustomerReport::where('begin', $begin)
            ->where('finish', $finish)
            ->first();

        if (! $report) {
            return false;
        }

        $users = User::whereIsCustomer(1)->whereIsActive(1)
            ->with('purchaseOrders')
            ->get();

        foreach ($users as $user) {
            $orders =  $user->purchaseOrders->where("success_at", ">=", $begin)->where("success_at", "<=", $finish);
            
            if ($orders->count() > 0) {
                echo $user->symbol_name."\n";
                $total_item = $total_service = $total_ship = $total_amount = 0;
        
                foreach ($orders as $order) {
                    $total_item += str_replace(",", "", $order->sumItemPrice()) * $order->current_rate;
                    $total_service += $order->purchase_order_service_fee * $order->current_rate;
                    $total_ship += $order->sumShipFee() * $order->current_rate;
                    $total_amount += $order->amount(false) * $order->current_rate;
                }

                $flag = PurchaseCustomerReportDetail::whereReportId($report->id)
                    ->whereUserId($user->id)
                    ->first();

                $data = [
                    'report_id' =>  $report->id,
                    'user_id'   =>  $user->id,
                    'count' =>  $orders->count(),
                    'total_price_items'    =>  number_format($total_item, 0, '.', ''),
                    'total_service'    =>  number_format($total_service, 0, '.', ''),
                    'total_ship'    =>  number_format($total_ship, 0, '.', ''),
                    'total_amount'    =>  number_format($total_amount, 0, '.', '')
                ];

                if ($flag) {
                    // update
                    $flag->update($data);
                } else {
                    // create
                    PurchaseCustomerReportDetail::create($data);
                }
            }
            
        }
    }
}
