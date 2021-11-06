<?php

namespace App\Admin\Actions\Customer;

use App\Admin\Services\OrderService;
use App\Admin\Services\UserService;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\PurchaseOrder\PurchaseOrderItem;
use App\Models\System\Warehouse;
use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class CreateOrderInCart extends BatchAction
{
    public $name = 'Tạo đơn hàng';
    protected $selector = '.create-purchase-order';

    /**
     * {@inheritdoc}
     */
    public function actionScript()
    {
        $warning = __('Vui lòng chọn sản phẩm');

        return <<<SCRIPT
        var key = $.admin.grid.selected();
        
        if (key.length == 0) {
            $.admin.toastr.error('{$warning}', '', {positionClass: 'toast-top-center'});
            return ;
        } else if (key.length > 30) {
            $.admin.toastr.error('Vui lòng chọn tối đa 30 link sản phẩm', '', {positionClass: 'toast-top-center'});
            return ;
        }
        
        Object.assign(data, {_key:key});
SCRIPT;
    }
    
    public function handle(Collection $collection, Request $request)
    {
        $ids = [];
        foreach ($collection as $model) {
            $ids[] = $model->id;
        }

        $items = PurchaseOrderItem::select('id', 'shop_name')->whereIn('id', $ids)->get();

        $shop = [];
        foreach ($items as $item) {
            $shop[trim($item->shop_name)][] = $item->id;
        }

        foreach ($shop as $shop_name => $row) {
            if (is_array($row) && sizeof($row) > 0) {
                $service = new OrderService();
                $item_total_amount = $service->getItemTotalAmount($row);
            
                $order = [
                    'shop_name'     =>  $shop_name,
                    'order_number'  =>  $service->generateOrderNR(),
                    'customer_id'   =>  Admin::user()->id,
                    'status'        =>  $service->getStatus('new-order'),
                    'deposited'     =>  0,
                    'customer_note' =>  null,
                    'admin_note'    =>  null,
                    'internal_note' =>  null,
                    'warehouse_id'  =>  $request->get('warehouse_id'),
                    'current_rate'  =>  $service->getCurrentRate(),
                    'supporter_order_id'            =>  Admin::user()->staff_order_id ?? null,
                    'supporter_sale_id'            =>  Admin::user()->staff_sale_id ?? null,
                    'purchase_order_service_fee'    =>  $service->calOrderService($item_total_amount, Admin::user()->percentService->percent),
                    'deposited_at'  =>  null,
                    'order_at'      =>  null,
                    'success_at'    =>  null,
                    'cancle_at'     =>  null,
                    'final_payment' =>  0,
                    'user_created_id'   =>  Admin::user()->id,
                    'user_deposited_at' =>  null,
                    'user_order_at'     =>  null,
                    'user_success_at'   =>  null,
                    'order_type'    =>  $request->order_type
                ];

                $order_res = PurchaseOrder::firstOrCreate($order);

                PurchaseOrderItem::whereIn('id', $row)->update([
                    'order_id'  =>  $order_res->id,
                    'status'    =>  $service->getItemStatus('in_order')
                ]);
            }
        }

        return $this->response()->success('Tạo đơn thành công')->redirect(route('admin.purchase_orders.index'));
    }

    public function form()
    {
        $this->select('warehouse_id', 'Kho hàng')->options(Warehouse::pluck('name', 'id'))->rules(['required'])->default(2);

        $radio = [
            '1688, Taobao'  =>  '1688, Taobao',
            'Wechat'    =>  'Wechat'
        ];

        if (Admin::user()->is_used_pindoudou == 1) {
            $radio['Pindoudou'] =   'Pindoudou';
        }
        $this->radio('order_type', 'Loại đơn hàng')->options($radio)->default('1688, Taobao')->stacked();
    }

    public function html()
    {
        return "<a class='create-purchase-order btn btn-sm btn-danger'><i class='fa fa-check'></i>&nbsp; ".$this->name."</a>";
    }

}