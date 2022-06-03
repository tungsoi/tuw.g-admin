<?php

namespace App\Admin\Controllers\Customer;

use App\Admin\Services\OrderService;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\PurchaseOrder\PurchaseOrderItem;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;

class CustomerPurchaseOrderController extends AdminController {

    protected function storeFromCart(Request $request) {

        $ids = explode(',', $request->get('ids'));

        $items = PurchaseOrderItem::select('id', 'shop_name')->whereIn('id', $ids)->get();

        $shop = [];
        foreach ($items as $item) {
            $shop[$item->shop_name][] = $item->id;
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
                    'supporter_order_id'            =>  1867, //Admin::user()->staff_order_id ?? null,
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
        
        admin_toastr('Tạo đơn hàng thành công', 'success');
        return redirect()->route('admin.purchase_orders.index');
    }
}