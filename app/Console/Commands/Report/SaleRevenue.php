<?php

namespace App\Console\Commands\Report;

use App\Admin\Services\SaleService;
use App\Models\SaleReport\Report;
use App\Models\SaleReport\ReportDetail;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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

            $ids = DB::connection('aloorder')
            ->table('admin_role_users')
            ->where('role_id', 3)
            ->get()->pluck('user_id');

            $sale_ids = User::select('id')->whereIn('id', $ids)->whereIsActive(1)->get()->pluck('id');

            ReportDetail::where('sale_report_id', $report->id)->delete();
            
            foreach ($sale_ids as $sale_id)
            {
                $service = new SaleService($sale_id, $report->begin_date, $report->finish_date);
                echo $service->username() . "\n";
                echo "\nbegin: ".date('H:i', strtotime(now()));

                $customers = $service->customers();
                $newCustomers = $customers->where('created_at', '>=', $report->begin_date. " 00:00:01")->where('created_at', '<=', $report->finish_date." 23:59:59");
                $successOrder = $service->successOrder();
                $successOrderNewCustomers =  $successOrder->whereIn('customer_id', $newCustomers->pluck('id'));
                $processingOrder = $service->processingOrder();
                $processingOrderNewCustomers = $processingOrder->whereIn('customer_id', $newCustomers->pluck('id'));
                $transportOrder = $service->transportOrder($customers->pluck('id'));
                $transportOrderNewCustomer = $transportOrder->whereIn('payment_customer_id', $newCustomers->pluck('id'));

                $data = [
                    'sale_report_id'    =>  $report->id,
                    'user_id'           =>  $sale_id,
                    'total_customer'    =>  $customers->count(),
                    'new_customer'      =>  $newCustomers->count(),
                    'total_customer_wallet' =>  $customers->where('wallet', '<', 0)->sum('wallet'),
                    'success_order'     =>  $successOrder->count(),
                    'success_order_payment' =>  $service->payment([], $successOrder),
                    'success_order_new_customer'   =>  $successOrderNewCustomers->count(),
                    'success_order_payment_new_customer'   =>  $service->payment($newCustomers->pluck('id'), $successOrder),
                    'success_order_service_fee' =>  $service->serviceFee($successOrder),
                    'processing_order'  =>  $processingOrder->count(),
                    'processing_order_payment'  =>  $service->payment([], $processingOrder),
                    'processing_order_new_customers'  =>  $processingOrderNewCustomers->count(),
                    'processing_order_payment_new_customer'    =>  $service->payment($newCustomers->pluck('id'), $processingOrder),
                    'processing_order_service_fee' =>  $service->serviceFee($processingOrder),
                    'total_transport_weight'    =>  $service->weight($transportOrder),
                    'total_transport_weight_new_customer'    =>  $service->weight($transportOrderNewCustomer),
                    'total_transport_fee'   =>  $service->transportFee($transportOrder),
                    'total_transport_fee_new_customer'   =>  $service->transportFee($transportOrderNewCustomer),
                    'transport_order'   =>  $transportOrder->count(),
                    'transport_order_new_customer'   =>  $transportOrderNewCustomer->count()
                ];

                $data['owed_processing_order_payment'] = $service->owed(
                    $data['processing_order_payment'],
                    $processingOrder
                );


                ReportDetail::firstOrCreate($data);

                echo "\nend: ".date('H:i', strtotime(now())) . "\n";
            }
    
            // ScheduleLog::create([
            //     'code'  =>  'update sale report ' . $begin_date . ' -> '.$finish_date
            // ]);
        }
        
    }
}
