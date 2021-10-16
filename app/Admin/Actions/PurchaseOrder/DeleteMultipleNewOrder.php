<?php

namespace App\Admin\Actions\PurchaseOrder;

use App\Admin\Services\OrderService;
use App\Admin\Services\UserService;
use App\Models\PurchaseOrder\PurchaseOrder;
use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class DeleteMultipleNewOrder extends BatchAction
{
    public $name = 'Xác nhận huỷ các đơn hàng mới đã chọn';
    protected $selector = '.cancel-multiple-new-order';

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
        foreach ($collection as $model) {
            $id = $model->id;

            $order = PurchaseOrder::find($id);
            $order->status = 10;
            $order->cancle_at = now();
            $order->user_cancle_at = Admin::user()->id;
            $order->save();
        }

        return $this->response()->success('Đã huỷ thành công')->refresh();
    }

    public function form()
    {
        $this->text('noti', 'Thông báo')->default('Xác nhận huỷ tất cả các đơn đã chọn ?')->disable();
    }

    public function html()
    {
        return "<a class='cancel-multiple-new-order btn btn-sm btn-warning'> <i class='fa fa-times'></i> Huỷ đơn đã chọn</a>";
    }

}