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

class SaleRevenue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sale-revenue-report:update {begin_date} {finish_date}';

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
        ini_set("memory_limit","2560M");

        $begin_date = $this->argument('begin_date');
        $finish_date = $this->argument('finish_date');

        $report = Report::whereBeginDate($begin_date)->whereFinishDate($finish_date)->orderBy('id', 'desc')->first();
        
        if (strtotime($finish_date." 23:59:59") < strtotime(now()))
        {
            echo "overtime";
            return false;
        }

        if ($report) {
            $begin_time = $report->begin_date. " 00:00:01";
            $end_time = $report->finish_date." 23:59:59";
            $report->updated_at = date('Y-m-d H:i:s', strtotime(now()));
            $report->save();

            $service = new UserService();

            $sale_users = $service->GetListSaleEmployee();
            $sale_ids = array_keys($sale_users->toArray());

            ReportDetail::where('sale_report_id', $report->id)->delete();
            
            foreach ($sale_ids as $sale_id)
            {
                echo $sale_id . "\n";

                // nvkd
                $sale_user = User::where('id', $sale_id)->with('saleCustomers')->first();

                // all khach hang
                $customers = $sale_user->saleCustomers;
                $customer_ids = $customers->pluck('id');
                $total_customer = $customers->count();
                $total_customer_wallet = number_format($customers->where('wallet', '<', 0)->sum('wallet'), 0, '.', '');

                // khach hang moi
                $new_customers = $customers->where('created_at', '>=', $begin_time)->where('created_at', '<=', $end_time);
                $new_customer_ids = $new_customers->pluck('id');
                $total_new_customers = $new_customers->count();

                if ($total_customer > 0) {
                    // don hang thanh toan
                    $payment_orders = PaymentOrder::where('status', 'payment_export')
                    ->where('export_at', '>=', $begin_time)
                    ->where('export_at', '<=', $end_time)
                    ->whereIn('payment_customer_id', $customer_ids)
                    ->with('transportCode')
                    ->get();

                    // don hang thanh toan khach hang moi
                    $payment_orders_new_customer = $payment_orders->whereIn('payment_customer_id', $new_customer_ids);
                    
                    // don hang mua ho thanh cong
                    $success_purchase_orders = PurchaseOrder::whereIn('customer_id', $customer_ids)->where('status', 9)
                                                    ->where('deposited_at', '>=', $begin_time)
                                                    ->where('deposited_at', '<=', $end_time)
                                                    ->where('success_at', '>=', $begin_time)
                                                    ->where('success_at', '<=', $end_time);
                    $success_offer_cn = 0;
                    $success_offer_vn = 0;
                    foreach ($success_purchase_orders->get() as $order) {
                        $offer_cn = $order->offer_cn != NULL ? $order->offer_cn : 0;
                        $offer_vn = $order->offer_vn != NULL ? $order->offer_vn : 0;
                        $success_offer_cn += str_replace(",", "",  $offer_cn);
                        $success_offer_vn += str_replace(",", "",  $offer_vn);
                    }
                                
                    $success_order = $success_purchase_orders->count();

                    $success_order_payment = number_format($this->amount($success_purchase_orders->get()), 0, '.', '');
                    $success_order_payment_rmb =  number_format($this->amount($success_purchase_orders->get(), true, 'rmb'), 0, '.', '');
                    $success_order_service_fee = number_format($this->serviceFee($success_purchase_orders->get()), 0, '.', '');
                                               
                    $success_purchase_orders_new_customer = $success_purchase_orders->whereIn('customer_id', $new_customer_ids)->get();

                    $ordering_orders = PurchaseOrder::whereIn('customer_id', $customer_ids)->where('status', 4)
                    ->where('deposited_at', '>=', $report->begin_date . " 00:00:01")
                    ->where('deposited_at', '<=', $report->finish_date ." 23:59:59")
                    ->get();

                    $ordering_offer_cn = 0;
                    $ordering_offer_vn = 0;
                    foreach ($ordering_orders as $order) {
                        $offer_cn = $order->offer_cn != NULL ? $order->offer_cn : 0;
                        $offer_vn = $order->offer_vn != NULL ? $order->offer_vn : 0;
                        $ordering_offer_cn += str_replace(",", "",  $offer_cn);
                        $ordering_offer_vn += str_replace(",", "",  $offer_vn);
                    }
            
                    $ordered_orders = PurchaseOrder::whereIn('customer_id', $customer_ids)->whereIn('status', [5, 7])
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
            
                    $processing_order =  $ordering_orders->merge($ordered_orders);
                    $processing_order_payment = number_format($this->amount($processing_order), 0, '.', '');
                    $processing_order_payment_rmb = number_format($this->amount($processing_order, true, 'rmb'), 0, '.', '');
                    $owed_processing_order_payment = number_format($this->amount($processing_order, false), 0, '.', '');
                    $processing_order_service_fee = number_format($this->serviceFee($processing_order), 0, '.', '');

                    $processing_order_new_customers = $processing_order->whereIn('customer_id', $new_customers->pluck('id'));
                    $processing_order_payment_new_customer = number_format($this->amount($processing_order_new_customers), 0, '.', '');
                    
                    // $total_transport_weight = TransportCode::whereIn('order_id', $payment_orders->pluck('id'))->sum('kg');
                    // $total_transport_weight_new_customer = TransportCode::whereIn('order_id', $payment_order_new_customer->pluck('id'))->sum('kg');
                    
                    $total_cn = $success_offer_cn + $ordering_offer_cn + $ordered_offer_cn;
                    $total_vn = $success_offer_vn + $ordering_offer_vn + $ordered_offer_vn;

                    $data = [
                        'sale_report_id'    =>  $report->id, // done 
                        'user_id'           =>  $sale_id, // done
                        'total_customer'    =>  $total_customer, // done
                        'new_customer'      =>  $total_new_customers,
                        'total_customer_wallet' =>  $total_customer_wallet,
                        'success_order'     =>  $success_order,
                        'success_order_payment' =>  $success_order_payment,
                        'success_order_payment_rmb' =>  $success_order_payment_rmb,
                        'success_order_new_customer'   =>  $success_purchase_orders_new_customer->count(),
                        'success_order_payment_new_customer'   =>  number_format($this->amount($success_purchase_orders_new_customer), 0, '.', ''),
                        'success_order_service_fee' =>  $success_order_service_fee,
                        'processing_order'  =>  $processing_order->count(),
                        'processing_order_payment'  =>  $processing_order_payment,
                        'processing_order_payment_rmb'  =>  $processing_order_payment_rmb,
                        'processing_order_new_customers'  =>  $processing_order_new_customers->count(),
                        'processing_order_payment_new_customer'    =>  $processing_order_payment_new_customer,
                        'processing_order_service_fee' =>  $processing_order_service_fee,
                        'total_transport_weight'    =>  $payment_orders->sum('total_kg'),
                        'total_transport_m3'    =>  $payment_orders->sum('total_m3'),
                        'total_transport_weight_new_customer'    =>  $payment_orders_new_customer->sum('total_kg'),
                        'total_transport_m3_new_customer'    =>  $payment_orders_new_customer->sum('total_m3'),
                        'total_transport_fee'   =>  $payment_orders->sum('amount'),
                        'total_transport_fee_new_customer'   =>  $payment_orders_new_customer->sum('amount'),
                        'transport_order'   =>  $payment_orders->count(),
                        'transport_order_new_customer'   =>  $payment_orders_new_customer->count(),
                        'owed_processing_order_payment' =>  $owed_processing_order_payment,
                        'offer_cn'  =>  number_format($total_cn, 2, '.', ''),
                        'offer_vn'  =>  number_format($total_vn, 0, '.', ''),
                        'customer_ids'  =>  json_encode($customer_ids),
                        'new_customer_ids' => json_encode($new_customer_ids)
                    ];

                    ReportDetail::firstOrCreate($data);

                    echo "\nend: ".date('H:i', strtotime(now())) . "\n";
                }
                
            }
    
            ScheduleLog::create([
                'name'  =>  'update sale report ' . $begin_date . ' -> '.$finish_date
            ]);
        }
        
    }

    public function amount($orders, $type = true, $money = 'vnd') {
        $total = 0;
        $owed = 0;

        foreach ($orders as $order) {
            $amount = (float) str_replace(",","", $order->amount());

            if ($money == 'vnd') {
                $amount_vnd = ($amount * $order->current_rate);
                $total += $amount_vnd;
                $owed += $amount_vnd - $order->deposited;
            } else {
                $amount_vnd = $amount;
                $total += $amount_vnd;
            }
        }

        return $type ? $total : $owed;
    }

    public function serviceFee($orders) {
        $total = 0;

        foreach ($orders as $order) {
            $purchase_order_service_fee = $order->purchase_order_service_fee != null 
                ? $order->purchase_order_service_fee
                : 0;

            $total += (str_replace(",", "", $purchase_order_service_fee) * $order->current_rate);
        }

        return $total;
    }
}
