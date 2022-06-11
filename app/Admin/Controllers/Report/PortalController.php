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
                $row->column(12, function (Column $column) {
                    $column->append((new Box('Doanh thu nạp tiền kho ngày: ' . $this->today, $this->revenueWarehouse())));
                });

                $row->column(6, function (Column $column) {
                    $column->append((new Box('Doanh thu nạp tiền kế toán ngày: ' . $this->today, $this->revenueAr())));
                });
            })
            ->row(function (Row $row) {
                $row->column(12, function (Column $column) {
                    $first_day = date('Y-m-01', strtotime(now()));
                    $last_day = date('Y-m-t', strtotime(now()));
                    $text = 'Doanh thu vận chuyển kho / Từ ' . $first_day . " đến " . $last_day;
                    $column->append((new Box($text, $this->revenueOrderWarehouse(false))));
                });
            })
            ->row(function (Row $row) {
                $row->column(12, function (Column $column) {
                    $column->append((new Box('Tiền dự trù đặt hàng (Toàn thời gian)', $this->estimateAmountBooking())));
                });
            })
            ->row(function (Row $row) {
                $row->column(12, function (Column $column) {
                    $column->append((new Box('Số liệu hàng tồn trong kho (Toàn thời gian)', $this->inWarehouseOrder())));
                });
            })
            ->row(function (Row $row) {
                $row->column(12, function (Column $column) {
                    $column->append((new Box('Hàng về trong ngày: ' . $this->today, $this->receiveToday())));
                });
            })
            ->row(function (Row $row) {
                $row->column(12, function (Column $column) {
                    $column->append((new Box('Báo cáo phòng kinh doanh tháng ' . date('m-Y', strtotime(now())), $this->saleRevenue())));
                });
            });
    }

    public function indexRebuildApi() {
        $first_day = date('Y-m-01', strtotime(now()));
        $last_day = date('Y-m-t', strtotime(now()));
        return response()->json([
            'status'    =>  200,
            'data'      =>  [
                [
                    'title'  =>  'Doanh thu nạp tiền kho ngày: ' . $this->today,
                    'childs' =>  [] //$this->revenueWarehouse(false)
                ],
                [
                    'title' =>  'Doanh thu nạp tiền kế toán ngày: '.$this->today,
                    'childs'    =>  [] //$this->revenueAr(false)
                ],
                [
                    'title' =>  "Doanh thu vận chuyển kho / Từ $first_day đến $last_day",
                    'childs'    =>  [] //$this->revenueOrderWarehouse(false)
                ],
                [
                    'title' =>  "Tiền dự trù đặt hàng (Toàn thời gian)",
                    'childs'    => [] // $this->calculatorEstimateAmountBooking(false)
                ],
                [
                    'title' =>  "Số liệu hàng tồn trong kho (Toàn thời gian)",
                    'childs'    =>  [] //$this->inWarehouseOrder(false)
                ],
                [
                    'title'     =>  'Hàng về trong ngày: '.$this->today,
                    'childs'    =>  [] //$this->receiveToday(false)
                ],
                [
                    'title'     =>  'Báo cáo phòng kinh doanh tháng ' . date('m-Y', strtotime(now())),
                    'childs'    =>  [] //$this->saleRevenue(false)
                ]
            ]
        ]);
    }

    public function revenueOrderWarehouse($type = false) {
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
        if (! $type) {
            try {
                return view('admin.system.report.revenue_order_warehouse', compact('warehouses', 'revenue', 'route_total'))->render();
            } catch (\Exception $e) {
                dd($e->getMessage());
            }
           
        } else {
            dd('api');
            // for api
            $data = [];

            $total_count = $total_money = 0;

            foreach ($warehouses as $key => $warehouse) {
                $data[] = [
                    $warehouse->name    =>  number_format($revenue[$warehouse->id]['cash_money']) . " VND",
                    "Đơn hàng"  =>  $revenue[$warehouse->id]['count']
                ];
                $total_count += $revenue[$warehouse->id]['count'];
                $total_money += $revenue[$warehouse->id]['cash_money'];
            }

            array_unshift($data, [
                "Tổng"  =>  number_format($total_money) . " VND",
                "Đơn hàng" => $total_count
            ]);

            return $data;
        }
        
    }

    public function weightPortal() {
        $codes = TransportCode::where('vietnam_receive_at', 'like', date('Y-m-d', strtotime(now()))."%")->get();

        return view('admin.system.report.wallet_weight', compact('codes'))->render();

    }

    public function receiveToday($type = true) {

        $first_day = date('Y-m-d', strtotime(now())) . " 00:00:01";
        $last_day = date('Y-m-d', strtotime(now())) . " 23:59:59";

        $codes = TransportCode::select('kg', 'm3')->whereBetween('vietnam_receive_at', [$first_day, $last_day])->get();

        $route = route('admin.transport_codes.index')
                    . "?vietnam_receive_at%5Bstart%5D=".date('Y-m-d', strtotime(now()))."&vietnam_receive_at%5Bend%5D=".date('Y-m-d', strtotime(Carbon::now()->addDays(1)));

        if ($type) {
            return view('admin.system.report.receive_today', compact('codes', 'route'))->render();
        } else {
            // for api
            $data = [
                [
                    "Mã vận đơn về hôm nay"  =>  $codes->count(),
                    "Tổng cân"  =>  $codes->sum('kg'),
                    "Tổng M3"   =>  $codes->sum('m3')
                ]
            ];

            return $data;
        }
        
    }

    protected function revenueWarehouse($type = true) // true: html, false: api
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

        if ($type) {
            return view('admin.system.report.revenue_warehouse', compact('warehouses', 'revenue'))->render();
        } else {
            // for api
            $data = [];
            foreach ($warehouses as $warehouse) {
                $data[] = [
                    $warehouse->name => (number_format($revenue[$warehouse->id]['cash_banking'] + $revenue[$warehouse->id]['cash_money'])) . " VND",
                    "Giao dịch" =>  $revenue[$warehouse->id]['count'],
                    "Tiền mặt"  =>  number_format($revenue[$warehouse->id]['cash_money']) . " VND",
                    "Chuyển khoản"  =>  number_format($revenue[$warehouse->id]['cash_banking']) . " VND"
                ];
            }

            return $data;
        }

    }

    protected function revenueAr($type = true)
    {
        $userIdsArRole = RoleUser::whereRoleId(5)->pluck('user_id');

        $transactions = Transaction::select('id', 'money', 'type_recharge')
                ->where('money', '!=', 0)
                ->where('created_at', 'like', $this->today.'%')
                ->whereIn('user_id_created', $userIdsArRole)
                ->whereIn('type_recharge', [0,1])
                ->get();

        $cash_money = Transaction::select('id', 'money', 'type_recharge')
        ->where('money', '!=', 0)
        ->where('created_at', 'like', $this->today.'%')
        ->whereIn('user_id_created', $userIdsArRole)
        ->where('type_recharge', 0)
        ->sum('money');

        $cash_banking = Transaction::select('id', 'money', 'type_recharge')
        ->where('money', '!=', 0)
        ->where('created_at', 'like', $this->today.'%')
        ->whereIn('user_id_created', $userIdsArRole)
        ->where('type_recharge', 1)
        ->sum('money');

        $revenue = [
            'cash_money'    =>  $cash_money,
            'cash_banking'  =>  $cash_banking,
            'count'         =>  $transactions->count(),
            'route'     =>  route('admin.transactions.index') ."?content=&"
                    . "type_recharge%5B%5D=0&type_recharge%5B%5D=1"
                    . "&user_id_created%5B%5D="
                    . implode("&user_id_created%5B%5D=", $userIdsArRole->toArray())
                    . "&created_at%5Bstart%5D=".date('Y-m-d', strtotime(now()))."&created_at%5Bend%5D=".date('Y-m-d', strtotime(Carbon::now()->addDays(1)))
        ];

        if ($type) {
            return view('admin.system.report.revenue_ar', compact('revenue'))->render();
        } else {
            // for api
            $data = [];
            $data[] = [
                'Phòng kế toán' =>  (number_format($revenue['cash_banking'] + $revenue['cash_money'])) . " VND",
                'Giao dịch' =>  $revenue['count'],
                'Tiền mặt'  =>  number_format($revenue['cash_money']) . " VND",
                'Chuyển khoản'  =>  number_format($revenue['cash_banking']) . " VND"
            ];

            return $data;
        }
        
    }

    public function estimateAmountBooking() {
        $route = route('admin.purchase_orders.index') . "?status=4";
        return view('admin.system.report.estimate_amount_booking', compact('route'))->render();
    }

    public function calculatorEstimateAmountBooking($type = true) {

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
    
            if ($type) {

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
            } else {
                // for api
                $data = [];
                $data[] = [
                    "Tổng dự trù đặt hàng"  =>  number_format($estimate) . " VND",
                    "Số đơn hàng"   =>  $orders->count(),
                    "Tổng tiền sản phẩm"    =>  number_format($total_vnd) . " VND",
                    "Tổng tiền cọc" =>  number_format($deposited) . " VND",
                ];

                return $data;
            }
    }

    public function inWarehouseOrder($type = true) {

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

        if ($type ) {
            return view('admin.system.report.in_warehouse_order', compact('revenue', 'warehouses'))->render();
        } else {
            $data = [];
            $total_count = $total_money = 0;
            foreach ($warehouses as $key => $warehouse) {
                $data[] = [
                    $warehouse->name    =>  number_format($revenue[$warehouse->id]['money']) . " VND",
                    "Số đơn hàng"   =>  $revenue[$warehouse->id]['count']
                ];
                $total_count += $revenue[$warehouse->id]['count'];
                $total_money += $revenue[$warehouse->id]['money'];
            }

            array_unshift($data, [
                "Tổng"  =>  number_format($total_money) . " VND",
                "Số đơn hàng"   =>  $total_count
            ]); 

            return $data;
        }

        
    }

    public function saleRevenue($type = true) {
        $month = date('Y-m', strtotime(now()));

        $report = Report::where('begin_date', 'like', $month.'%')->where('finish_date', 'like', $month.'%')->first();
        $process = $success = 0;
        if ($report) 
        {
            $detail = $report->reportDetail();

            $order = new UserService();
            $sales = $order->GetListSaleEmployee();
    
            $process = $detail->sum('processing_order_payment');
            $success = $detail->sum('success_order_payment');
    
            $total = $process + $success;
            $route = route('admin.revenue_reports.show', $report->id) . "?mode=new&portal=false";
        }
        

        if ($type) {
            return view('admin.system.report.sale_revenue', compact('detail', 'sales', 'success', 'process', 'total', 'route'))->render();
        } else {
            $data = [
                [
                    "Tổng doanh số tháng"   =>  number_format($process + $success) . " VND",
                    "Số nhân viên kinh doanh"   =>  $sales->count(),
                    "Đơn chưa hoàn thành (".$detail->sum('processing_order').")"   =>  number_format($process) . " VND",
                    "Đơn hoàn thành (".$detail->sum('success_order').")"   =>  number_format($success) . " VND",
                ]
            ];

            return $data;
        }
        
    }
}
