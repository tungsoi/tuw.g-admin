<?php

namespace App\Admin\Actions\TransportCode;

use App\Admin\Services\OrderService;
use App\Admin\Services\UserService;
use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ConfirmSwapWarehouse extends BatchAction
{
    public $name = 'Xác nhận đã nhận hàng luân chuyển kho';
    protected $selector = '.confirm-swap-warehouse';

    /**
     * {@inheritdoc}
     */
    public function actionScript()
    {
        $warning = __('Vui lòng chọn Mã vận đơn');

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
            $model->status = $orderService->getTransportCodeStatus('vietnam-rev');
            $model->finish_swap_warehouse_at = now();
            $model->finish_swap_user_id = Admin::user()->id;
            $model->ware_house_id = $model->ware_house_swap_id;
            $model->save();
        }

        return $this->response()->success('Đã xác nhận thành công')->refresh();
    }

    public function form()
    {
        $this->text('noti', 'Thông báo')->default('Bạn xác nhận rằng các mã vận đơn đã chọn được chuyển về kho luân chuyển thành công ?')->disable();
    }

    public function html()
    {
        return "<a class='confirm-swap-warehouse btn btn-sm btn-success'><i class='fa fa-check'></i>&nbsp; Xác nhận hàng luân chuyển kho</a>";
    }

}