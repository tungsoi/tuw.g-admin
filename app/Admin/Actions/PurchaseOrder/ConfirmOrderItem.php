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

    /**
     * {@inheritdoc}
     */
    public function actionScript()
    {
        $warning = __('Vui lòng chọn sản phẩm');

        return <<<SCRIPT
        var key = $.admin.grid.selected();
        
        if (key.length === 0) {
            $.admin.toastr.error('{$warning}', '', {positionClass: 'toast-top-center'});
            return ;
        }
        
        Object.assign(data, {_key:key});
SCRIPT;
    }

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
        return "<a class='confirm-order-item btn btn-sm btn-primary'>Xác nhận đã đặt hàng</a>";
    }

}