<?php

namespace App\Console\Commands\Report;

use App\Admin\Services\UserService;
use App\Models\PaymentOrder\PaymentOrder;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\SaleReport\Report;
use App\User;
use Illuminate\Console\Command;
use App\Models\SaleReport\SaleSalary as SaleSalaryModel;
use App\Models\SaleReport\SaleSalaryDetail;
use App\Models\System\ScheduleLog;

class SaleSalary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sale:salary {begin_date} {finish_date}';

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

        // $begin_date = $this->argument('begin_date');
        // $finish_date = $this->argument('finish_date');

        // $report = Report::whereBeginDate($begin_date)
        //     ->whereFinishDate($finish_date)
        //     ->orderBy('id', 'desc')
        //     ->first();
        
        // if (strtotime($finish_date." 23:59:59") < strtotime(now()))
        // {
        //     echo "overtime";
        //     return false;
        // }

        // $details = SaleSalaryModel::where('report_id', $report->id)->get();
        
        // SaleSalaryModel::where('report_id', $report->id)->delete();
        // SaleSalaryDetail::whereIn('sale_salary_id', $details->pluck('id'))->delete();

        // $begin = $report->begin_date." 00:00:01";
        // $finish = $report->finish_date." 23:59:59";
        // $service = new UserService();

        // $sale_users = $service->GetListSaleEmployee();
        // $sale_ids = array_keys($sale_users->toArray());

        // $employees = User::whereIn('id', $sale_ids)->with('saleCustomers')->get();

        // foreach ($employees as $employee) {
        //     echo $employee->name."\n";

        //     $all_customers = $employee->saleCustomers();
        //     $new_customers = $employee->saleCustomers()->whereBetween('created_at', [$begin, $finish]);
        //     $old_customers = $employee->saleCustomers()->whereNotIn('id', $new_customers->pluck('id'));

        //     $po_success = PurchaseOrder::whereStatus(9)
        //         ->whereBetween('success_at', [$begin, $finish])
        //         ->whereIn('customer_id', $all_customers->pluck('id'))
        //         ->with('items')
        //         ->get();
            
        //     $po_not_success = PurchaseOrder::whereIn('status', [4, 5, 7])
        //         ->whereIn('customer_id', $all_customers->pluck('id'))
        //         ->with('items')
        //         ->get();
            
        //     $transport_orders = PaymentOrder::whereStatus('payment_export')
        //         ->whereBetween('export_at', [$begin, $finish])
        //         ->whereIn('payment_customer_id', $all_customers->pluck('id'))
        //         ->with('transportCode')
        //         ->get();

        //     $data = [
        //         'report_id'    =>    $report->id,
        //         'user_id'    =>    $employee->id,
        //         'new_customer_ids'  =>  json_encode($new_customers->pluck('id')),
        //         'old_customer_ids'  =>  json_encode($old_customers->pluck('id')),
        //         'new_customer'    =>    $new_customers->count(),
        //         'old_customer'    =>    $old_customers->count(),
        //         'all_customer'    =>    $all_customers->count(),
        //         'owed_wallet_new_customer'    =>  $this->wallet($new_customers->get()),
        //         'owed_wallet_old_customer'    =>  $this->wallet($old_customers->get()),
        //         'owed_wallet_all_customer'    =>  $this->wallet($all_customers->get()),
        //         'po_success'    =>    $po_success->count(),
        //         'po_success_all_customer'    =>  $this->amount($po_success),
        //         'po_success_old_customer'    =>  $this->amount($po_success->whereIn('customer_id', $old_customers->pluck('id'))),
        //         'po_success_new_customer'    =>     $this->amount($po_success->whereIn('customer_id', $new_customers->pluck('id'))),
        //         'po_success_service_fee'    =>  $this->serviceFee($po_success),
        //         'po_success_total_rmb'    =>    $this->amount($po_success, true, "rmb"),
        //         'po_success_offer'    =>    $this->offer($po_success),
        //         'po_not_success'    =>    $po_not_success->count(),
        //         'po_not_success_all_customer'    =>    $this->amount($po_not_success),
        //         'po_not_success_old_customer'    =>    $this->amount($po_not_success->whereIn('customer_id', $old_customers->pluck('id'))),
        //         'po_not_success_new_customer'    =>    $this->amount($po_not_success->whereIn('customer_id', $new_customers->pluck('id'))),
        //         'po_not_success_service_fee'     =>    $this->serviceFee($po_not_success),
        //         'po_not_success_owed'    =>    $this->owed($po_not_success),
        //         'po_not_success_deposited'   =>  $this->deposited($po_not_success),
        //         'transport_order'    =>    $transport_orders->count(),
        //         'trs_kg_new_customer'    =>    $this->kg($transport_orders->whereIn('payment_customer_id', $new_customers->pluck('id'))->sum('total_kg')),
        //         'trs_m3_new_customer'    =>    $this->m3($transport_orders->whereIn('payment_customer_id', $new_customers->pluck('id'))->sum('total_m3')),
        //         'trs_kg_old_customer'    =>    $this->kg($transport_orders->whereIn('payment_customer_id', $old_customers->pluck('id'))->sum('total_kg')),
        //         'trs_m3_old_customer'    =>    $this->m3($transport_orders->whereIn('payment_customer_id', $old_customers->pluck('id'))->sum('total_m3')),
        //         'trs_kg_all_customer'    =>    $this->kg($transport_orders->sum('total_kg')),
        //         'trs_m3_all_customer'    =>    $this->m3($transport_orders->sum('total_m3')),
        //         'trs_amount_new_customer'    =>   number_format($transport_orders->whereIn('payment_customer_id', $new_customers->pluck('id'))->sum('amount'), 0, '.', ''),
        //         'trs_amount_old_customer'    =>   number_format($transport_orders->whereIn('payment_customer_id', $old_customers->pluck('id'))->sum('amount'), 0, '.', ''),
        //         'trs_amount_all_customer'    =>   number_format($transport_orders->sum('amount'), 0, '.', ''),
        //         'employee_salary'    =>    0,
        //         'employee_working_point'    =>  0
        //     ];

        //     $res = SaleSalaryModel::create($data);

        //     // fetch detail
        //     SaleSalaryDetail::where('sale_salary_id', $res->id)->delete();

        //     $customers = User::whereIn('id', $employee->saleCustomers()->pluck('id'))
        //     ->with('purchaseOrders')
        //     ->with('paymentOrders')
        //     ->get();
            
        //     foreach ($customers as $customer) {

        //         $po_success = $customer->purchaseOrders()->whereStatus(9)
        //         ->whereBetween('success_at', [$begin, $finish])
        //         ->with('items')
        //         ->get();

        //         $po_not_success = $customer->purchaseOrders()->whereIn('status', [4, 5, 7])
        //         ->with('items')
        //         ->get();
                
        //         $transport_orders = $customer->paymentOrders()->whereStatus('payment_export')
        //         ->whereBetween('export_at', [$begin, $finish])
        //         ->with('transportCode')
        //         ->get();

        //         $fetch = [
        //             'sale_salary_id'    =>  $res->id,
        //             'customer_id'   =>  $customer->id,
        //             'wallet'    =>  number_format($customer->wallet, 0, '.', ''),
        //             'po_success'    =>  $po_success->count(),
        //             'po_payment'    =>  $this->amount($po_success),
        //             'po_service_fee'    =>  $this->serviceFee($po_success),
        //             'po_rmb'    =>  $this->amount($po_success, true, "rmb"),
        //             'po_offer'    =>  $this->offer($po_success),
        //             'po_not_success'    =>  $po_not_success->count(),
        //             'po_not_success_payment'    =>   $this->amount($po_not_success),
        //             'po_not_success_service_fee'    =>  $this->serviceFee($po_not_success),
        //             'po_not_success_deposite'    =>  $this->deposited($po_not_success),
        //             'po_not_success_owed'    =>  $this->owed($po_not_success),
        //             'trs'    =>  $transport_orders->count(),
        //             'trs_kg'    =>  $this->kg($transport_orders->sum('total_kg')),
        //             'trs_m3'    =>  $this->m3($transport_orders->sum('total_m3')),
        //             'trs_payment'    =>  number_format($transport_orders->sum('amount'), 0, '.', ''),
        //         ];

        //         SaleSalaryDetail::create($fetch);
        //     }

        // }

        ScheduleLog::create([
            'name'  =>  'update sale salary '
        ]);

    }
    public function m3($m3) {
        return number_format($m3, 3, '.', '');
    }

    public function kg($kg) {
        return number_format($kg, 1, '.', '');
    }

    public function deposited($orders) {
        return (int) number_format($orders->sum('deposited'), 0, '.', '');
    }

    public function owed($orders) {
        $amount =  $this->amount($orders);
        $deposite = $orders->sum('deposited');
        return (int) number_format($amount-$deposite, 0, '.', '');
    }

    public function wallet($customers) {
        $total = $customers->where('wallet', '<', 0)->sum('wallet');
        return number_format($total, 0, '.', '');
    }

    public function amount($orders, $type = true, $money = 'vnd') {
        $total = 0;
        $owed = 0;

        foreach ($orders as $order) {
            $amount = (float) str_replace(",","", $order->amount());

            if ($money == 'vnd') {
                $amount_vnd = ($amount * $order->current_rate);
                $amount_vnd = number_format($amount_vnd, 0, '.', '');
                $total += $amount_vnd;
                $owed += $amount_vnd - $order->deposited;
            } else {
                $amount_vnd = (float) number_format($amount, 2, '.', '');
                $total += $amount_vnd;
            }
        }

        if ($money == 'vnd') { 
            $total = number_format($total, 0, '.', '');
        } else {
            $total = number_format($total, 2, '.', '');
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

        return (int) number_format($total, 0, '.', '');
    }

    public function offer($orders) {
        $offer = 0;
        foreach ($orders as $order) {
            $offer_vn = $order->offer_vn != NULL ? $order->offer_vn : 0;
            $offer += str_replace(",", "",  $offer_vn);
        }

        return $offer;

    }
}
