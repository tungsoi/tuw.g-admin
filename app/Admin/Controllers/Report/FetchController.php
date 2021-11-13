<?php

namespace App\Admin\Controllers\Report;

use App\Models\PaymentOrder\PaymentOrder;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\SaleReport\ReportDetail;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Grid;
use App\Models\SaleReport\Report;
use App\Models\TransportOrder\TransportCode;
use App\User;

class FetchController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title;

    public function __construct()
    {
        $this->title = 'BÁO CÁO KINH DOANH';
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ReportDetail());
        $grid->model()->where('id', $_GET['id']);
        $data = ReportDetail::find($_GET['id']);
        $report = $data->report;
        $user = User::find($data->user_id);

        $type = isset($_GET['type']) ? $_GET['type'] : null;

        if ($type == "new_customer") 
        {
            $customers = User::select('id', 'username', 'symbol_name', 'created_at', 'wallet')
            ->where('staff_sale_id', $user->id)
            ->whereBetween('created_at', [$report->begin_date, $report->finish_date])
            ->get();
        } else {
            $customers = User::select('id', 'username', 'symbol_name', 'created_at', 'wallet')
            ->whereIsActive(1)
            ->where('staff_sale_id', $user->id)->get();
        }
        $total = 0;
        $m3 = 0;
        $count = 0;
        $amount = 0;
        foreach ($customers as $customer) {
            $orders = PaymentOrder::where('status', 'payment_export')
                        ->where('export_at', '>=', $report->begin_date .' 00:00:01')
                        ->where('export_at', '<=', $report->finish_date . ' 23:59:59')
                        ->where('payment_customer_id', $customer->id)
                        ->with('transportCode')
                        ->get();

            $total_order = $orders->sum('total_kg');
            $total_m3 = $orders->sum('total_m3');
            $count_order = $orders->count();
            $total_amount = $orders->sum('amount');
            // foreach ($orders as $order) {
            //    $total_order
            // }
            
            $customer->weight = $total_order;
            $customer->m3 = $total_m3;
            $customer->count_order = $count_order;
            $customer->amount = $total_amount;

            $total += $total_order;
            $m3 += $total_m3;
            $count += $count_order;
            $amount += $total_amount;

            // $last_action = [
            //     'purchase_created'  =>  PurchaseOrder::select('created_at', 'customer_id')
            //     ->where('customer_id', $customer->id)
            //     ->orderBy('created_at', 'desc')
            //     ->first()
            //     ->created_at ?? "",
            //     'purchase_deposited'    =>  PurchaseOrder::select('deposited_at', 'customer_id')
            //     ->where('customer_id', $customer->id)
            //     ->orderBy('deposited_at', 'desc')
            //     ->first()
            //     ->deposited_at ?? "",
            //     'transport_created' =>  PaymentOrder::select('created_at', 'payment_customer_id')
            //     ->where('payment_customer_id', $customer->id)
            //     ->orderBy('created_at', 'desc')
            //     ->first()
            //     ->created_at ?? ""
            // ];
            // $customer->last_action = $last_action;

        }
        $grid->header(function () use ($customers, $data, $total, $m3, $report, $count, $amount) {
            
            return view('admin.salereport.fetch', compact('customers', 'data', 'total', 'm3', 'report', 'count', 'amount'));
        });

        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->disableFilter();
        $grid->disableActions();
        $grid->disablePagination();

        return $grid;
    }
}
