<?php

namespace App\Admin\Controllers\Report;

// use App\Admin\Actions\Exporter\SaleReportExporter;
// use App\Models\ReportDetailBackup;

use App\Admin\Services\UserService;
use App\Console\Commands\SyncData\Users;
use App\Models\PaymentOrder\PaymentOrder;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\SaleReport\Report;
use App\Models\SaleReport\ReportDetail;
use App\Models\Setting\RoleUser;
use App\Models\System\TeamSale as SystemTeamSale;
use App\Models\System\Transaction;
use App\Models\System\Warehouse;
use App\Models\System\WeightPortal;
use App\Models\TransportOrder\TransportCode;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\User;
use Carbon\Carbon;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\DB;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\InfoBox;
use Encore\Admin\Layout\Column;
use Encore\Admin\Widgets\Box;

class PortalController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title;
    protected $today;

    public function __construct()
    {
        $this->title = 'Tổng quan báo cáo - thống kê';
        $this->today = date('Y-m-d', strtotime(now()));
    }

    public function indexRebuild(Content $content)
    {   
        Admin::style('
            .box-body [class*="col-"] {
                padding-left: 0px !important;
            }
        ');
        
        return $content
            ->title($this->title)
            ->row(function (Row $row) {
                $row->column(12, function (Column $column)
                {
                    $column->append((new Box('Doanh thu nạp tiền kho ngày: ' . $this->today, $this->revenueWarehouse())));
                });

                $row->column(6, function (Column $column)
                {
                    $column->append((new Box('Doanh thu nạp tiền kế toán ngày: ' . $this->today, $this->revenueAr())));
                });   
            })
            ->row(function (Row $row) {
                $row->column(12, function (Column $column)
                {
                    $first_day = date('Y-m-01', strtotime(now()));
                    $last_day = date('Y-m-t', strtotime(now()));
                    $text = 'Doanh thu vận chuyển kho / Từ ' . $first_day . " đến " . $last_day;
                    $column->append((new Box($text, $this->revenueOrderWarehouse())));
                });
            })
            ->row(function (Row $row) {
                $row->column(12, function (Column $column)
                {
                    $column->append((new Box('Tiền dự trù đặt hàng (Toàn thời gian)', $this->estimateAmountBooking())));
                });   
            })
            ->row(function (Row $row) {
                $row->column(12, function (Column $column)
                {
                    $column->append((new Box('Số liệu hàng tồn trong kho (Toàn thời gian)', $this->inWarehouseOrder())));
                });   
            })
            ->row(function (Row $row) {
                $row->column(12, function (Column $column)
                {
                    $column->append((new Box('Hàng về trong ngày: ' . $this->today, $this->receiveToday())));
                });   
            })
            ->row(function (Row $row) {
                $row->column(12, function (Column $column)
                {
                    $column->append((new Box('Báo cáo phòng kinh doanh tháng ' . date('m-Y', strtotime(now())), $this->saleRevenue())));
                });   
            });
    }

    public function revenueOrderWarehouse() {
        $warehouses = Warehouse::all();
        $revenue = [];
        
        $first_day = date('Y-m-01', strtotime(now()));
        $last_day = date('Y-m-t', strtotime(now()));

        foreach ($warehouses as $warehouse) {

            $orders = PaymentOrder::select('amount')
                        ->where('payment_customer_id', '!=', 0)
                        ->where('status', 'payment_export')
                        ->whereBetween('export_at', [$first_day, $last_day])
                        ->where('warehouse_id', $warehouse->id)
                        ->get();

            $revenue[$warehouse->id] = [
                'cash_money'    =>  $orders->sum('amount'),
                'count'         =>  $orders->count(),
                'route'         =>  route('admin.payments.all')
                                    . "?status=payment_export"
                                    . "&warehouse_id=" . $warehouse->id
                                    . "&export_at%5Bstart%5D=".date('Y-m-', strtotime(now()))."01&export_at%5Bend%5D=".date('Y-m-', strtotime(now())).cal_days_in_month(CAL_GREGORIAN, date('m', strtotime(now())), date('Y', strtotime(now())))
            ];
        }

        $route_total = route('admin.payments.all')
        . "?status=payment_export"
        . "&export_at%5Bstart%5D=".date('Y-m-', strtotime(now()))."01&export_at%5Bend%5D=".date('Y-m-', strtotime(now())).cal_days_in_month(CAL_GREGORIAN, date('m', strtotime(now())), date('Y', strtotime(now())));
        return view('admin.system.report.revenue_order_warehouse', compact('warehouses', 'revenue', 'route_total'))->render();
    }

    public function weightPortal() {
        $codes = TransportCode::where('vietnam_receive_at', 'like', date('Y-m-d', strtotime(now()))."%")->get();

        return view('admin.system.report.wallet_weight', compact('codes'))->render();

    }

    public function receiveToday() {

        $first_day = date('Y-m-d', strtotime(now())) . " 00:00:01";
        $last_day = date('Y-m-d', strtotime(now())) . " 23:59:59";

        $codes = TransportCode::select('kg', 'm3')->whereBetween('vietnam_receive_at', [$first_day, $last_day])->get();

        $route = route('admin.transport_codes.index')
                    . "?vietnam_receive_at%5Bstart%5D=".date('Y-m-d', strtotime(now()))."&vietnam_receive_at%5Bend%5D=".date('Y-m-d', strtotime(Carbon::now()->addDays(1)));

        return view('admin.system.report.receive_today', compact('codes', 'route'))->render();
    }

    protected function revenueWarehouse()
    {
        $warehouses = Warehouse::all();
        $revenue = [];

        foreach ($warehouses as $warehouse) {
            $members = $warehouse->employees;
            $transactions = Transaction::select('money', 'type_recharge')
                ->where('money', '!=', 0)
                ->whereIn('type_recharge', [0, 1])
                ->where('created_at', 'like', $this->today.'%')
                ->whereIn('user_id_created', $members)
                ->get();
                
            $revenue[$warehouse->id] = [
                'cash_money'    =>  $transactions->where('type_recharge', 0)->sum('money'),
                'cash_banking'  =>  $transactions->where('type_recharge', 1)->sum('money'),
                'count'         =>  $transactions->count(),
                'route'     =>  route('admin.transactions.index') ."?content=&"
                    . "type_recharge%5B%5D=0&type_recharge%5B%5D=1"
                    . "&user_id_created%5B%5D="
                    . implode("&user_id_created%5B%5D=", $members)
                    . "&created_at%5Bstart%5D=".date('Y-m-d', strtotime(now()))."&created_at%5Bend%5D=".date('Y-m-d', strtotime(Carbon::now()->addDays(1)))
            ];
        }

        return view('admin.system.report.revenue_warehouse', compact('warehouses', 'revenue'))->render();
    }

    protected function revenueAr()
    {
        $userIdsArRole = RoleUser::whereRoleId(5)->pluck('user_id');

        $transactions = Transaction::select('id', 'money', 'type_recharge')
                ->where('money', '!=', 0)
                ->where('created_at', 'like', $this->today.'%')
                ->whereIn('user_id_created', $userIdsArRole)
                ->get();

        $revenue = [
            'cash_money'    =>  $transactions->where('type_recharge', 0)->sum('money'),
            'cash_banking'  =>  $transactions->where('type_recharge', 1)->sum('money'),
            'count'         =>  $transactions->count(),
            'route'     =>  route('admin.transactions.index') ."?content=&"
                    . "type_recharge%5B%5D=0&type_recharge%5B%5D=1"
                    . "&user_id_created%5B%5D="
                    . implode("&user_id_created%5B%5D=", $userIdsArRole->toArray())
                    . "&created_at%5Bstart%5D=".date('Y-m-d', strtotime(now()))."&created_at%5Bend%5D=".date('Y-m-d', strtotime(Carbon::now()->addDays(1)))
        ];

        return view('admin.system.report.revenue_ar', compact('revenue'))->render();
    }

    public function estimateAmountBooking() {
        $route = route('admin.purchase_orders.index') . "?status=4";
        return view('admin.system.report.estimate_amount_booking', compact('route'))->render();
    }

    public function calculatorEstimateAmountBooking() {

            $orders = PurchaseOrder::select('id', 'deposited', 'current_rate')
                ->whereStatus(4)
                ->with('items')
                ->orderBy('id', 'desc')
                ->get();
            $total_vnd = 0;
            $deposited = $orders->sum('deposited');
    
            foreach ($orders as $order){
                $amount = (float) str_replace(",","", $order->sumItemPrice());
                $total_vnd += $amount * $order->current_rate;
            }
    
            $estimate = $total_vnd - $deposited;
    
            return response()->json([
                'status'    =>  true,
                'flag'      =>  'call',
                'data'      =>  [
                    'number_orders' =>  $orders->count(),
                    'total_vnd' =>  number_format($total_vnd),
                    'total_deposited'   =>  number_format($deposited),
                    'total_estimate'    =>  number_format($estimate)
                ]
            ]);
    }

    public function inWarehouseOrder() {

        $warehouses = Warehouse::all();
        $revenue = [];

        foreach ($warehouses as $warehouse) {

            $orders = PaymentOrder::select('amount')
            ->where('payment_customer_id', '!=', 0)
            ->where('status', 'payment_not_export')
            ->where('warehouse_id', $warehouse->id)
            ->get();

            $revenue[$warehouse->id] = [
                'count'    =>  $orders->count(),
                'money'    =>  $orders->sum('amount'),
                'route'         =>  route('admin.payments.all')
                                    . "?status=payment_not_export"
                                    . "&warehouse_id=".$warehouse->id
            ];
        }

        return view('admin.system.report.in_warehouse_order', compact('revenue', 'warehouses'))->render();
    }

    public function saleRevenue() {
        $month = date('Y-m', strtotime(now()));

        $report = Report::where('begin_date', 'like', $month.'%')->where('finish_date', 'like', $month.'%')->first();
        $detail = $report->reportDetail();

        $order = new UserService();
        $sales = $order->GetListSaleEmployee();

        $process = $detail->sum('processing_order_payment');
        $success = $detail->sum('success_order_payment');

        $total = $process + $success;
        $route = route('admin.revenue_reports.show', $report->id) . "?mode=new&portal=false";
        return view('admin.system.report.sale_revenue', compact('detail', 'sales', 'success', 'process', 'total', 'route'))->render();
    }
}
