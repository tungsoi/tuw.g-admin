<?php

namespace App\Admin\Controllers\Report;

use App\Admin\Services\UserService;
use App\Models\PaymentOrder\PaymentOrder;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\System\Transaction;
use App\Models\TransportOrder\TransportCode;
use App\User;
use Carbon\Carbon;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Illuminate\Support\Facades\DB;

class TrackingPaymentOrderController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title;

    public function __construct()
    {
        $this->title = 'Đối soát số liệu đơn hàng thanh toán';
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        ini_set('memory_limit', '6400M');
        $orders = PaymentOrder::select('created_at', 'order_number', 'total_kg', 'total_m3', 'id', 'discount_value', 'discount_type', 'is_sub_customer_wallet_weight', 'total_sub_wallet_weight')
        ->where('export_at', '>', '2021-11-17 00:00:01')
        // ->where('export_at', '<', '2021-10-3 00:00:00')
        ->with('transportCode')
        ->where('status', '!=', 'cancel')
        ->orderBy('id', 'desc')
        ->get();

        // dd($orders);
        $ids = [];
        foreach ($orders as $order) {
            $total_kg = $order->total_kg;
            $total_m3 = $order->total_m3;
            if ($order->discount_type == 1) {
                $total_kg += $order->discount_value;
            } else {
                $total_kg -= $order->discount_value;
            }

            if ($order->is_sub_customer_wallet_weight == 1) {
                $total_kg += str_replace('.0', '', number_format($order->total_sub_wallet_weight, 1, '.', ''));
            }

            $total_kg_items = $order->transportCode->where('payment_type', 1)->sum('kg');
            $total_m3_items = $order->transportCode->where('payment_type', -1)->sum('m3');
            $total_m3_items = number_format($total_m3_items, 3, '.', '');

            // dd($total_kg != $total_kg_items || $total_m3 != $total_m3_items);
            // dd($total_m3 != $total_m3_items);
            // dd([
            //     $order->order_number, $total_kg, $total_m3, $total_kg_items, $total_m3_items
            // ]);

            $total_kg = number_format($total_kg, 1, '.', '');
            $total_kg_items = number_format($total_kg_items, 1, '.', '');
            $total_m3 = number_format($total_m3, 3, '.', '');
            $total_m3_items = number_format($total_m3_items, 3, '.', '');

            if ($total_kg_items != $total_kg) {
                $ids[] = $order->id;
            } else {
                if ($total_m3 != $total_m3_items) {
                    $ids[] = $order->id;
                }
            }
            // if (($total_kg != $total_kg_items) || ($total_m3 != $total_m3_items)) {
            //     dd($order->id);
            //     $ids[] = $order->id;
            // }
        }

        $grid = new Grid(new PaymentOrder());
        $grid->model()
        ->whereIn('id', $ids)
        ->with('transportCode')
        ->orderBy('id', 'desc');

        $grid->header(function () {
            return "Lấy các đơn hàng thanh toán xuất kho từ ngày 01/09/2021 đén " . now();
        });
        $grid->disableFilter();
        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT')->style('text-align: center');
        $grid->id('#Id');
        $grid->order_number('Mã đơn hàng');
        $grid->status('Trạng thái')->display(function () {
            $data = [
                'amount_rmb'   =>  [
                    'is_label'   =>  true,
                    'color'     =>  $this->statusColor(),
                    'text'      =>  $this->statusText()
                ]
            ];
            return view('admin.system.core.list', compact('data'));
        });
        $grid->export_at('Ngày xuất kho')->display(function () {
            return date('d/m/Y', strtotime($this->export_at));
        });
        $grid->total_kg('Tổng cân');
        $grid->total_m3('Tổng M3');
        $grid->total_kg_items('Tổng cân MVD thanh toán ra')->display(function () {
            return $this->transportCode->where('payment_type', 1)->sum('kg');
        });
        $grid->total_m3_items('Tổng m3 MVD thanh toán ra')->display(function () {
            // dd($this->transportCode->where('payment_type', -1));
            return $this->transportCode->where('payment_type', -1)->sum('m3');
        });

        $grid->disableActions();
        $grid->disableCreateButton();
        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->disableExport();

        return $grid;
    }
}