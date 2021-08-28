<?php

namespace App\Admin\Actions\PurchaseOrder;

use App\Admin\Services\OrderService;
use App\Admin\Services\UserService;
use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ConfirmOrderItem extends BatchAction
{
    public $name = 'Xác nhận đã đặt hàng sản phẩm';
    protected $selector = '.confirm-order-item';

    public function handle(Collection $collection, Request $request)
    {
        $orderService = new OrderService();

        foreach ($collection as $model) {
            $model->status = $orderService->getItemStatus('wait_order');
            $model->order_at = now();
            $model->save();
        }

        return $this->response()->success('Đã xác nhận thành công')->refresh();
    }

    public function form()
    {
        $this->text('noti', 'Thông báo')->default('Xác nhận đã đặt hàng các sản phẩm này ?')->disable();
    }

    public function html()
    {
        return "<a class='confirm-order-item btn btn-sm btn-primary'><i class='fa fa-check'></i>&nbsp; Xác nhận đã đặt hàng</a>";
    }

}