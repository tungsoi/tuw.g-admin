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
        ->where('created_at', '>', '2021-09-01 00:00:01')
        ->with('transportCode')
        ->orderBy('id', 'desc')
        ->get();
        
        foreach ($orders as $order) {
            $total_kg = $order->total_kg;
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
        }

        dd($orders->count());

        $grid = new Grid(new PurchaseOrder());
        $grid->model()
        ->whereIn('status', [5,7,9])
        ->where('deposited_at', '>', Carbon::now()->subDays(30))
        ->where('transport_code', 'like', '%,%')
        ->orderBy('id', 'desc');

        $grid->disableFilter();
        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT')->style('text-align: center');

        $grid->disableActions();
        $grid->disableCreateButton();
        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->disableExport();

        return $grid;
    }
}