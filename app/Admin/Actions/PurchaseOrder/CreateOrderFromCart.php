<?php

namespace App\Admin\Actions\PurchaseOrder;

use App\Admin\Services\OrderService;
use App\Models\Alilogi\Warehouse;
use App\Models\ExchangeRate;
use App\Models\OrderItem;
use App\Models\PurchaseOrder;
use App\Models\System\Warehouse as SystemWarehouse;
use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreateOrderFromCart extends BatchAction
{
    const MAXIMUM_LINK = 30;

    protected $selector = ".create-order";

    public $name = 'Tạo đơn hàng';

    public function handle(Collection $collection, Request $request)
    {
        // DB::beginTransaction();
        // try {
            
        //     if ($collection->count() > self::MAXIMUM_LINK) 
        //     {
        //         return $this->response()->success('Số link sản phẩm tối đa trong 1 đơn hàng là '. self::MAXIMUM_LINK.'. Vui lòng tạo lại đơn hàng.')->refresh();
        //     }

        //     $service = new OrderService();
        //     $order_number = $service->generateOrderNR();

        //     $exchange_rate = ExchangeRate::first()->vnd;

        //     // tao ban ghi don hang
        //     $order = PurchaseOrder::create([
        //         'order_number'  =>  $order_number,
        //         'customer_id'   =>  Admin::user()->id,
        //         'order_type'    =>  1, // order // 2: transport
        //         'warehouse_id'  =>  $request->warehouse_id[0],
        //         'current_rate'  =>  $exchange_rate,
        //         'status'        =>  PurchaseOrder::STATUS_NEW_ORDER,
        //         'customer_name' =>  Admin::user()->symbol_name
        //     ]);

        //     // tong gia tri san pham
        //     $purchase_total_items_price = 0;

        //     // tien coc mac dinh
        //     $deposit_default = 0;

        //     foreach ($collection as $model) {
        //         OrderItem::find($model->id)->update([
        //             'order_id'  =>  $order->id,
        //             'status'    =>  OrderItem::STATUS_PURCHASE_ITEM_NOT_ORDER,
        //             'qty_reality'   =>  $model->qty
        //         ]);

        //         $purchase_total_items_price += ($model->qty * $model->price); // Te
        //     }

        //     $percent = PurchaseOrder::PERCENT_NUMBER[Admin::user()->customer_percent_service];
        //     $purchase_service_fee_percent = Admin::user()->customer_percent_service;
        //     if (is_null($percent)) {
        //         $percent = 1;
        //     }
        //     $purchase_order_service_fee = number_format($purchase_total_items_price / 100 * $percent, 2); // phi dich vu vnd

        //     $final_total_price = round(($purchase_total_items_price + $purchase_order_service_fee) * $exchange_rate); // vnd
        //     $deposit_default   = round($final_total_price * 70 / 100); // tiền cọc = 70% tiền tổng đơn

        //     PurchaseOrder::find($order->id)->update([
        //         'purchase_total_items_price'    =>  $purchase_total_items_price,
        //         'final_total_price'             =>  $final_total_price,
        //         'deposit_default'               =>  $deposit_default,
        //         'purchase_order_service_fee'    =>  $purchase_order_service_fee,
        //         'supporter_id'                  =>  Admin::user()->staff_sale_id ?? "",
        //         'purchase_service_fee_percent'  =>  $purchase_service_fee_percent
        //     ]);
            
        //     DB::commit();

        //     admin_success('Tạo đơn hàng thành công. Vui lòng liên hệ với bộ phận Sale để tiến hành đặt cọc cho đơn hàng này.');
        //     return $this->response()->success('Tạo đơn hàng thành công')->refresh();
        // } 
        // catch (\Exception $e) {
        //     DB::rollBack();
        //     return $this->response()->success('Đã xảy ra lỗi, vui lòng thử lại')->refresh();
        // }
        
    }

    public function form()
    {
        $warehouse = SystemWarehouse::whereIsActive(1)->get();
        $data = [];

        foreach ($warehouse as $wh) {
            $data[$wh->id] = $wh->name . " (" . $wh->address . ") ";
        }
        $this->checkbox('warehouse_id', 'Chọn Kho nhận hàng')->options($data)->default(1);
    }

    public function html()
    {
        return "<a class='create-order btn btn-sm btn-danger'><i class='fa fa-cart-plus'></i>&nbsp; ".$this->name."</a>";
    }

    public function actionScript()
    {
        $warning = __('Vui lòng chọn sản phẩm');

        return <<<SCRIPT
        var key = $.admin.grid.selected();
        
        if (key.length === 0) {
            $.admin.toastr.warning('{$warning}', '', {positionClass: 'toast-top-center'});
            return ;
        }
        
        Object.assign(data, {_key:key});
SCRIPT;
    }

}