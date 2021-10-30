<?php

namespace App\Admin\Actions\PurchaseOrder;

use App\Admin\Services\OrderService;
use App\Admin\Services\UserService;
use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ConfirmVnReceiveItem extends BatchAction
{
    public $name = 'Xác nhận sản phẩm đã về Việt Nam';
    protected $selector = '.confirm-vn-receive-item';

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
            $model->status = $orderService->getItemStatus('vn_received');
            $model->vn_receive_at = now();
            $model->user_confirm_receive = Admin::user()->id;
            $model->save();
        }

        return $this->response()->success('Đã xác nhận thành công')->refresh();
    }

    public function form()
    {
        $this->text('noti', 'Thông báo')->default('Xác nhận đã về Việt Nam các sản phẩm này ?')->disable();
    }

    public function html()
    {
        return "<a class='confirm-vn-receive-item btn btn-sm btn-warning'>Xác nhận đã về Việt Nam</a>";
    }

}