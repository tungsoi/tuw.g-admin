<?php

namespace App\Console\Commands\Report;

use App\Admin\Services\UserService;
use App\Models\PaymentOrder\PaymentOrder;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\SaleReport\Report;
use App\Models\SaleReport\ReportDetail;
use App\Models\System\ScheduleLog;
use App\Models\TransportOrder\TransportCode;
use App\User;
use Illuminate\Console\Command;

class PaymentWeightSaleReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sale_report:weight';

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

        $_1customer_ids = User::whereIsCustomer(User::CUSTOMER)
        ->whereIsActive(User::ACTIVE)
        ->whereNull('staff_sale_id')
        ->pluck('id');


        $orders_non_sale = PaymentOrder::select('id')->where('status', 'payment_export')
                        ->where('export_at', '>=', '2021-10-01 00:00:01')
                        ->where('export_at', '<=', '2021-11-01 00:00:01')
                        ->whereIn('payment_customer_id', $_1customer_ids)
                        ->with('transportCode')
                        ->get()
                        ->pluck('id');
// $count  = 0;
// $total_kg = 0;
// $total_vnd = 0;
//             if ($orders->count() > 0) {
//                 $count += $orders->count();
//                 foreach ($orders as $order) {
//                     $ids[] = $order->id;
//                     $total_kg += $order->total_kg;
//                     $total_vnd += $order->amount;
//                 }
//             }

            // echo "- Count:  $count \n";
            // echo "- Tổng cân:  $total_kg \n";
            // echo "- Tổng tiền:  ".number_format($total_vnd)." \n";

            // dd('oke');
            
        $begin_date = "2021-10-01";
        $finish_date = "2021-10-31";

        $report = Report::whereBeginDate($begin_date)->whereFinishDate($finish_date)->orderBy('id', 'desc')->first();
        
        // if (strtotime($finish_date." 23:59:59") < strtotime(now()))
        // {
        //     echo "overtime";
        //     return false;
        // }

        if ($report) {

            $service = new UserService();

            $sale_users = $service->GetListSaleEmployee();
            $sale_ids = array_keys($sale_users->toArray());
            

            $total_kg = 0;
            $total_vnd = 0;
            $count = 0;
            $ids = [];
            foreach ($sale_ids as $sale_id)
            {
                $sale_user = User::find($sale_id);
                echo $sale_user->name . "\n";

                $customers = $sale_user->saleCustomers();
                $customer_ids = $customers->pluck('id');
                $total_customer = $customers->count();

                if ($total_customer > 0) {
                    $orders = PaymentOrder::where('status', 'payment_export')
                        ->where('export_at', '>=', '2021-10-01 00:00:01')
                        ->where('export_at', '<=', '2021-11-01 00:00:01')
                        ->whereIn('payment_customer_id', $customer_ids)
                        ->with('transportCode')
                        ->get();

                    if ($orders->count() > 0) {
                        $count += $orders->count();
                        foreach ($orders as $order) {
                            $ids[] = $order->id;
                            $total_kg += $order->total_kg;
                            $total_vnd += $order->amount;
                        }
                    }
                }
            }

            echo "- Count:  $count \n";
            echo "- Tổng cân:  $total_kg \n";
            echo "- Tổng tiền:  ".number_format($total_vnd)." \n";

            $ids = array_merge($ids, $orders_non_sale);
            $check = PaymentOrder::whereNotIn('id', $ids)
            ->where('export_at', '>=', '2021-10-01 00:00:01')
            ->where('export_at', '<=', '2021-11-01 00:00:01')
            ->where('status', '!=', 'cancel')
            ->get();

            dd($check->pluck('order_number'));
        }
    }
}
