<?php

namespace App\Admin\Actions\TransportCode;

use App\Admin\Services\OrderService;
use App\Admin\Services\UserService;
use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class PaymentNotExport extends BatchAction
{
    public $name = 'Thanh toán chưa xuất kho';
    protected $selector = '.confirm-payment-not-export';

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

        $ids = [];
        foreach ($collection as $model) {
            $ids[] = $model->id;
        }

        $ids_route = implode(',', $ids);

        $route = route('admin.payments.index', ['ids' => $ids_route]) . "?type=payment_not_export";

        return $this->response()->redirect($route);
    }

    public function form()
    {
        $this->text('noti', 'Thông báo')->default('Xác nhận thanh toán chưa xuất kho các mã vận đơn đã chọn ?')->disable();
    }

    public function html()
    {
        return "<a class='confirm-payment-not-export btn btn-sm btn-info'><i class='fa fa-pause'></i>&nbsp; Thanh toán chưa xuất kho</a>";
    }

}