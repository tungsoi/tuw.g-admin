<?php

namespace App\Admin\Actions\TransportCode;

use App\Admin\Services\OrderService;
use App\Admin\Services\UserService;
use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class Export extends BatchAction
{
    public $name = 'Xác nhận xuất kho các Mã vận đơn đã chọn';
    protected $selector = '.export-code';

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
            $model->status = $orderService->getTransportCodeStatus('success');
            $model->export_at = now();
            $model->save();
        }

        return $this->response()->success('Đã xác nhận thành công')->refresh();
    }

    public function form()
    {
        $this->text('noti', 'Thông báo')->default('Bạn xác nhận Thanh toán tạm các Mã vận đơn đã chọn ?')->disable();
    }

    public function html()
    {
        return "<a class='export-code btn btn-sm btn-danger'><i class='fa fa-check'></i>&nbsp; Xuất kho</a>";
    }

}