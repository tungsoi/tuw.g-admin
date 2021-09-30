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
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\User;
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
                $row->column(8, function (Column $column)
                {
                    $column->append((new Box('Doanh thu kho / ' . $this->today, $this->revenueWarehouse())));
                });

                $row->column(4, function (Column $column)
                {
                    $column->append((new Box('Doanh thu kế toán / ' . $this->today, $this->revenueAr())));
                });   
            })
            ->row(function (Row $row) {
                $row->column(12, function (Column $column)
                {
                    $column->append((new Box('Tiền dự trù đặt hàng', $this->estimateAmountBooking())));
                });   
            })
            ->row(function (Row $row) {
                $row->column(12, function (Column $column)
                {
                    $column->append((new Box('Số liệu hàng tồn trong kho', $this->inWarehouseOrder())));
                });   
            })
            ->row(function (Row $row) {
                $row->column(12, function (Column $column)
                {
                    $column->append((new Box('Hàng về trong ngày / ' . $this->today, "")));
                });   
            })
            ->row(function (Row $row) {
                $row->column(12, function (Column $column)
                {
                    $column->append((new Box('Báo cáo phòng kinh doanh / ' . date('Y-m', strtotime(now())), $this->saleRevenue())));
                });   
            })
            ->row(function (Row $row) {
                $row->column(12, function (Column $column)
                {
                    $column->append((new Box('Ví cân tổng hợp', "")));
                });   
            });
    }

    protected function revenueWarehouse()
    {
        $warehouses = Warehouse::all();
        $revenue = [];

        foreach ($warehouses as $warehouse) {
            $members = $warehouse->employees;
            $transactions = Transaction::select('money', 'type_recharge')
                ->where('money', '!=', 0)
                ->where('created_at', 'like', $this->today.'%')
                ->whereIn('user_id_created', $members)
                ->get();

            $revenue[$warehouse->id] = [
                'cash_money'    =>  $transactions->where('type_recharge', 0)->sum('money'),
                'cash_banking'  =>  $transactions->where('type_recharge', 1)->sum('money'),
                'count'         =>  $transactions->count()
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
            'count'         =>  $transactions->count()
        ];

        return view('admin.system.report.revenue_ar', compact('revenue'))->render();
    }

    public function estimateAmountBooking() {
        return view('admin.system.report.estimate_amount_booking')->render();
    }

    public function calculatorEstimateAmountBooking() {

            $orders = PurchaseOrder::select('id', 'deposited', 'current_rate')->whereStatus(4)->orderBy('id', 'desc')->get();
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
            $members = $warehouse->employees;

            $orders = PaymentOrder::where('status', 'payment_not_export')->whereIn('user_created_id', $members)->get();

            $revenue[$warehouse->id] = [
                'count'    =>  $orders->count(),
                'money'    =>  $orders->sum('amount')
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
        return view('admin.system.report.sale_revenue', compact('detail', 'sales', 'success', 'process', 'total'))->render();
    }
}
