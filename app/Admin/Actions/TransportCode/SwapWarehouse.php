<?php

namespace App\Admin\Actions\TransportCode;

use App\Admin\Services\OrderService;
use App\Admin\Services\UserService;
use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class SwapWarehouse extends BatchAction
{
    public $name = 'Luân chuyển kho';
    protected $selector = '.swap-warehouse';

    public function handle(Collection $collection, Request $request)
    {
        $orderService = new OrderService();

        foreach ($collection as $model) {
            if ($model->status != $orderService->getTransportCodeStatus('swap')) {
                $model->status = $orderService->getTransportCodeStatus('swap');
                $model->begin_swap_warehouse_at = now();
                $model->begin_swap_user_id = Admin::user()->id;
                $model->finish_swap_user_id = $request->finish_swap_user_id;
                $model->ware_house_swap_id = $request->ware_house_swap_id;
                $model->save();
            }
        }

        return $this->response()->success('Đã chuyển thông tin luân chuyển kho')->refresh();
    }

    public function form()
    {
        $userService = new UserService();
        $this->hidden('begin_swap_user_id')->default(Admin::user()->id);
        $this->select('ware_house_swap_id', 'Kho nhận hàng')->options($userService->GetListWarehouse());
        $this->select('finish_swap_user_id', 'Nhân viên xác nhận kho hàng luân chuyển đến kho')->options($userService->GetAllEmployee());
    }

    public function html()
    {
        return "<a class='swap-warehouse btn btn-sm btn-warning'><i class='fa fa-mail-reply'></i>&nbsp; Luân chuyển kho</a>";
    }

}