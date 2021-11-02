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

class OfferRevenue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:offer';

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
            
            foreach ($sale_ids as $sale_id)
            {
                echo $sale_id . "\n";
                $sale_user = User::find($sale_id);

                $customers = $sale_user->saleCustomers();
                $customer_ids = $customers->pluck('id');
                $total_customer = $customers->count();

                if ($total_customer > 0) {
                    $success_purchase_orders = PurchaseOrder::select('offer_cn', 'offer_vn')
                                                    ->whereIn('customer_id', $customer_ids)
                                                    ->where('status', 9)
                                                    ->where('deposited_at', '>=', $report->begin_date . " 00:00:01")
                                                    ->where('deposited_at', '<=', $report->finish_date ." 23:59:59")
                                                    ->where('success_at', '>=', $report->begin_date . " 00:00:01")
                                                    ->where('success_at', '<=', $report->finish_date ." 23:59:59")
                                                    ->get();

                    $success_offer_cn = 0;
                    $success_offer_vn = 0;
                    foreach ($success_purchase_orders as $order) {

                        $offer_cn = $order->offer_cn != NULL ? $order->offer_cn : 0;
                        $offer_vn = $order->offer_vn != NULL ? $order->offer_vn : 0;
                        $success_offer_cn += str_replace(",", "",  $offer_cn);
                        $success_offer_vn += str_replace(",", "",  $offer_vn);
                    }

                    $ordering_orders = PurchaseOrder::select('offer_cn', 'offer_vn')
                    ->whereIn('customer_id', $customer_ids)->where('status', 4)
                    ->where('deposited_at', '>=', $report->begin_date . " 00:00:01")
                    ->where('deposited_at', '<=', $report->finish_date ." 23:59:59")
                    ->get();

                    $ordering_offer_cn = 0;
                    $ordering_offer_vn = 0;
                    foreach ($ordering_orders as $order) {

                        try {
                            $offer_cn = $order->offer_cn != NULL ? $order->offer_cn : 0;
                            $offer_vn = $order->offer_vn != NULL ? $order->offer_vn : 0;
                            $ordering_offer_cn += str_replace(",", "",  $offer_cn);
                            $ordering_offer_vn += str_replace(",", "",  $offer_vn);
                        } catch (\Exception $e) {
                            dd($order);
                        }
                    }
            
                    $ordered_orders = PurchaseOrder::select('offer_cn', 'offer_vn')
                    ->whereIn('customer_id', $customer_ids)->whereIn('status', [5, 7])
                    ->where('deposited_at', '>=', $report->begin_date . " 00:00:01")
                    ->where('deposited_at', '<=', $report->finish_date ." 23:59:59")
                    ->where('order_at', '>=', $report->begin_date . " 00:00:01")
                    ->where('order_at', '<=', $report->finish_date ." 23:59:59")
                    ->get();

                    $ordered_offer_cn = 0;
                    $ordered_offer_vn = 0;
                    foreach ($ordered_orders as $order) {

                        $offer_cn = $order->offer_cn != NULL ? $order->offer_cn : 0;
                        $offer_vn = $order->offer_vn != NULL ? $order->offer_vn : 0;
                        $ordered_offer_cn += str_replace(",", "",  $offer_cn);
                        $ordered_offer_vn += str_replace(",", "",  $offer_vn);
                    }

                    $total_cn = $success_offer_cn + $ordering_offer_cn + $ordered_offer_cn;
                    $total_vn = $success_offer_vn + $ordering_offer_vn + $ordered_offer_vn;

                    $record = ReportDetail::where('user_id', $sale_id)
                        ->where('sale_report_id', $report->id)
                        ->first();

                    $record->offer_cn = number_format($total_cn, 2, '.', '');
                    $record->offer_vn = number_format($total_vn, 0, '.', '');
                    $record->save();
                }
                
            }
    
            // ScheduleLog::create([
            //     'name'  =>  'update sale report ' . $begin_date . ' -> '.$finish_date
            // ]);
        }
    }
}
