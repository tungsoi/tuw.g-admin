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
        ini_set("memory_limit","256M");

        $begin_date = $this->argument('begin_date');
        $finish_date = $this->argument('finish_date');

        $report = Report::whereBeginDate($begin_date)->whereFinishDate($finish_date)->orderBy('id', 'desc')->first();
        
        if (strtotime($finish_date." 23:59:59") < strtotime(now()))
        {
            echo "overtime";
            return false;
        }

        if ($report) {
            $report->updated_at = date('Y-m-d H:i:s', strtotime(now()));
            $report->save();

            $service = new UserService();

            $sale_users = $service->GetListSaleEmployee();
            $sale_ids = array_keys($sale_users->toArray());
            // $sale_ids = ['2156'];

            ReportDetail::where('sale_report_id', $report->id)->delete();
            
            foreach ($sale_ids as $sale_id)
            {
                echo $sale_id . "\n";
                $sale_user = User::find($sale_id);

                $customers = $sale_user->saleCustomers();
                $customer_ids = $customers->pluck('id');
                $total_customer = $customers->count();

                $total_customer_wallet = $customers->where('wallet', '<', 0)->sum('wallet');


                $new_customers = $sale_user->saleCustomers()->where('created_at', '>=', $report->begin_date. " 00:00:01")->where('created_at', '<=', $report->finish_date." 23:59:59")->get();
                $total_new_customers = $new_customers->count();

                if ($total_customer > 0) {
                    $payment_orders = PaymentOrder::whereIn('payment_customer_id', $customer_ids)
                                    ->where('created_at', '>=', $report->begin_date. " 00:00:01")->where('created_at', '<=', $report->finish_date." 23:59:59")
                                    ->get();

                    $payment_order_new_customer = PaymentOrder::whereIn('payment_customer_id', $customer_ids)
                                    ->whereIn('payment_customer_id', $new_customers->pluck('id'))
                                    ->where('created_at', '>=', $report->begin_date. " 00:00:01")->where('created_at', '<=', $report->finish_date." 23:59:59")
                                    ->get();
                    $success_purchase_orders = PurchaseOrder::whereIn('customer_id', $customer_ids)->where('status', 9)
                                                    ->where('deposited_at', '>=', $report->begin_date . " 00:00:01")
                                                    ->where('deposited_at', '<=', $report->finish_date ." 23:59:59")
                                                    ->where('success_at', '>=', $report->begin_date . " 00:00:01")
                                                    ->where('success_at', '<=', $report->finish_date ." 23:59:59");

                    $success_order = $success_purchase_orders->count();

                    $success_order_payment = number_format($this->amount($success_purchase_orders->get()), 0, '.', '');
                    $success_order_service_fee = number_format($this->serviceFee($success_purchase_orders->get()), 0, '.', '');
                                               
                    $success_purchase_orders_new_customer = $success_purchase_orders->whereIn('customer_id', $new_customers->pluck('id'))->get();

                    $ordering_orders = PurchaseOrder::whereIn('customer_id', $customer_ids)->where('status', 4)
                    ->where('deposited_at', '>=', $report->begin_date . " 00:00:01")
                    ->where('deposited_at', '<=', $report->finish_date ." 23:59:59")
                    ->get();
            
                    $ordered_orders = PurchaseOrder::whereIn('customer_id', $customer_ids)->whereIn('status', [5, 7])
                    ->where('deposited_at', '>=', $report->begin_date . " 00:00:01")
                    ->where('deposited_at', '<=', $report->finish_date ." 23:59:59")
                    ->where('order_at', '>=', $report->begin_date . " 00:00:01")
                    ->where('order_at', '<=', $report->finish_date ." 23:59:59")
                    ->get();
            
                    $processing_order =  $ordering_orders->merge($ordered_orders);
                    $processing_order_payment = number_format($this->amount($processing_order), 0, '.', '');
                    $owed_processing_order_payment = number_format($this->amount($processing_order, false), 0, '.', '');
                    $processing_order_service_fee = number_format($this->serviceFee($processing_order), 0, '.', '');

                    $processing_order_new_customers = $processing_order->whereIn('customer_id', $new_customers->pluck('id'));
                    $processing_order_payment_new_customer = number_format($this->amount($processing_order_new_customers), 0, '.', '');
                    
                    $total_transport_weight = TransportCode::whereIn('order_id', $payment_orders->pluck('id'))->sum('kg');
                    $total_transport_weight_new_customer = TransportCode::whereIn('order_id', $payment_order_new_customer->pluck('id'))->sum('kg');
                    $data = [
                        'sale_report_id'    =>  $report->id,
                        'user_id'           =>  $sale_id,
                        'total_customer'    =>  $total_customer,
                        'new_customer'      =>  $total_new_customers,
                        'total_customer_wallet' =>  number_format($total_customer_wallet, 0, '.', ''),
                        'success_order'     =>  $success_order,
                        'success_order_payment' =>  $success_order_payment,
                        'success_order_new_customer'   =>  $success_purchase_orders_new_customer->count(),
                        'success_order_payment_new_customer'   =>  number_format($this->amount($success_purchase_orders_new_customer), 0, '.', ''),
                        'success_order_service_fee' =>  $success_order_service_fee,
                        'processing_order'  =>  $processing_order->count(),
                        'processing_order_payment'  =>  $processing_order_payment,
                        'processing_order_new_customers'  =>  $processing_order_new_customers->count(),
                        'processing_order_payment_new_customer'    =>  $processing_order_payment_new_customer,
                        'processing_order_service_fee' =>  $processing_order_service_fee,
                        'total_transport_weight'    =>  $total_transport_weight,
                        'total_transport_weight_new_customer'    =>  $total_transport_weight_new_customer,
                        'total_transport_fee'   =>  $payment_orders->sum('amount'),
                        'total_transport_fee_new_customer'   =>  $payment_order_new_customer->sum('amount'),
                        'transport_order'   =>  $payment_orders->count(),
                        'transport_order_new_customer'   =>  $payment_order_new_customer->count(),
                        'owed_processing_order_payment' =>  $owed_processing_order_payment
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

    public function amount($orders, $type = true) {
        $total = 0;
        $owed = 0;

        foreach ($orders as $order) {
            $amount = (float) str_replace(",","", $order->amount());
            $amount_vnd = ($amount * $order->current_rate);
            $total += $amount_vnd;
            $owed += $amount_vnd - $order->deposited;
        }

        return $type ? $total : $owed;
    }

    public function serviceFee($orders) {
        $total = 0;

        foreach ($orders as $order) {
            $total += (str_replace(",", "", $order->purchase_order_service_fee) * $order->current_rate);
        }

        return $total;
    }
}
