<?php

namespace App\Admin\Actions\TransportCode;

use App\Admin\Services\OrderService;
use App\Admin\Services\UserService;
use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class PaymentExport extends BatchAction
{
    public $name = 'Thanh toán xuất kho';
    protected $selector = '.confirm-payment-export';

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
        $flag = true;
        $customer_name = [];
        foreach ($collection as $model) {
            $ids[] = $model->id;
            $customer_name[$model->customer_code_input] = $model->customer_code_input;
        }

        if (sizeof($customer_name) > 1) {
            // loi thanh toan 2 khach hang khac nhau
            return $this->response()->error('Thanh toán 2 mã khách hàng khác nhau.');
        } else {
            $ids_route = implode(',', $ids);

            $route = route('admin.payments.index', ['ids' => $ids_route]) . "?type=payment_export";
    
            return $this->response()->redirect($route);
        }   
    }

    public function form()
    {
        $this->text('noti', 'Thông báo')->default('Xác nhận thanh toán xuất kho các mã vận đơn đã chọn ?')->disable();
    }

    public function html()
    {
        return "<a class='confirm-payment-export btn btn-sm btn-primary'><i class='fa fa-download'></i>&nbsp; Thanh toán xuất kho</a>";
    }

}