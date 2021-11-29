<?php

namespace App\Admin\Actions\PurchaseOrder;

use App\Admin\Services\OrderService;
use App\Admin\Services\UserService;
use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ConfirmOutstockItem extends BatchAction
{
    public $name = 'Xác nhận hết hàng';
    protected $selector = '.confirm-outstock-item';

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
            $model->status = $orderService->getItemStatus('out_stock');
            $model->outstock_at = now();
            $model->qty_reality = 0;
            $model->user_confirm_outstock = Admin::user()->id;
            $model->save();
        }

        return $this->response()->success('Đã xác nhận thành công')->refresh();
    }

    public function form()
    {
        $this->text('noti', 'Thông báo')->default('Xác nhận hết hàng ?')->disable();
    }

    public function html()
    {
        return "<a class='confirm-outstock-item btn btn-sm btn-danger'>Xác nhận hết hàng</a>";
    }

}