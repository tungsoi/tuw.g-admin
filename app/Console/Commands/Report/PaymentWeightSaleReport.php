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

        // $off_user = User::whereIsActive(0)
        //     ->whereIsCustomer(0)
        //     ->get()
        //     ->pluck('id')
        //     ->toArray();

        // $customers = User::whereIn('staff_sale_id', $off_user)
        // // ->get();
        // ->update([
        //     'staff_sale_id' =>  4138
        // ]);

        // dd($customers);
//         $_1customer_ids = User::whereIsCustomer(User::CUSTOMER)
//         ->whereIsActive(User::ACTIVE)
//         ->whereNull('staff_sale_id')
//         ->pluck('id');


        // $orders_non_sale = PaymentOrder::select('id')->where('status', 'cancel')
        //                 ->where('export_at', '>=', '2021-10-01 00:00:01')
        //                 ->where('export_at', '<=', '2021-11-01 00:00:01')
                        // ->whereIn('payment_customer_id', $_1customer_ids)
                        // ->with('transportCode')
                        // ->get();

                        // dd($orders_non_sale->count());
                        // ->pluck('id');
// // $count  = 0;
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
            

            $count = 0;
            $ids = [];
            $arr = [];
            foreach ($sale_ids as $key => $sale_id)
            {

                // if ($sale_id == 1423) {
                    $sale_user = User::find($sale_id);
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

                            $total_kg = 0;
                            $total_vnd = 0;
                            $total_m3 = 0;
                            foreach ($orders as $order) {
                                $ids[] = $order->id;
                                $total_kg += $order->total_kg;
                                $total_vnd += $order->amount;
                                $total_m3 += $order->total_m3;
                            }
                            $record = ReportDetail::where('user_id', $sale_id)
                        ->where('sale_report_id', $report->id)
                        ->first();
                    
                            if ($record) {
                                $arr[] = [
                                    'user_name' =>    $sale_user->name,
                                    'user_id'   =>  $sale_id,
                                    'total_transport_weight'    =>  $total_kg,
                                    'total_transport_m3'   =>   $total_m3,
                                    'total_transport_fee'   =>  $total_vnd,
                                    'total_transport_weight_new_customer'   =>  0,
                                    'total_transport_fee_new_customer'  =>  0,
                                    'customer_id'   =>  json_encode($customer_ids)
                                ];
                            }
                        }
                    }
                // }
            }

            dd($arr);

            // $all = PaymentOrder::whereNotIn('id', $ids)
            // ->where('status', 'payment_export')
            // ->where('export_at', '>=', '2021-10-01 00:00:01')
            // ->where('export_at', '<=', '2021-11-01 00:00:01')
            // ->get();

            // dd($all->pluck('order_number'));
            

            // dd($ids);
            // $ids = array_merge($ids->toArray(), $orders_non_sale->toArray());
            // echo sizeof($ids) . "\n";
            // $check = PaymentOrder::whereNotIn('id', $ids)
            // ->whereNotIn('id', $orders_non_sale)
            // ->where('export_at', '>=', '2021-10-01 00:00:01')
            // ->where('export_at', '<=', '2021-11-01 00:00:01')
            // ->where('status', 'payment_export')
            // ->pluck('order_number');

            // echo "Đơn lệch - ". json_encode($check);

            // dd($check->pluck('order_number'));
        }
    }
}
