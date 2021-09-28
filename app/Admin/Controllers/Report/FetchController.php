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
            $customers = User::select('id', 'username', 'symbol_name', 'created_at', 'wallet')->where('staff_sale_id', $user->id)->get();
        }
        $total = 0;
        foreach ($customers as $customer) {
            $orders = PaymentOrder::select('id')->where('payment_customer_id', $customer->id)
            ->where('created_at', '>=', $report->begin_date)
            ->where('created_at', '<=', $report->finish_date)
            ->get();

            $total_order = 0;
            foreach ($orders as $order) {
                $items = TransportCode::select('kg')->where('order_id', $order->id)->sum('kg');
                $total_order += $items;
            }
            
            $customer->weight = $total_order;
            $total += $total_order;

            $last_action = [
                'purchase_created'  =>  PurchaseOrder::select('created_at', 'customer_id')
                ->where('customer_id', $customer->id)
                ->orderBy('created_at', 'desc')
                ->first()
                ->created_at ?? "",
                'purchase_deposited'    =>  PurchaseOrder::select('deposited_at', 'customer_id')
                ->where('customer_id', $customer->id)
                ->orderBy('deposited_at', 'desc')
                ->first()
                ->deposited_at ?? "",
                'transport_created' =>  PaymentOrder::select('created_at', 'payment_customer_id')
                ->where('payment_customer_id', $customer->id)
                ->orderBy('created_at', 'desc')
                ->first()
                ->created_at ?? ""
            ];
            $customer->last_action = $last_action;

        }
        $grid->header(function () use ($customers, $data, $total) {
            
            return view('salereport.fetch', compact('customers', 'data', 'total'));
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
