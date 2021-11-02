<?php

namespace App\Admin\Actions\PaymentOrder;

use App\Admin\Services\OrderService;
use App\Admin\Services\UserService;
use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class MergeOrder extends BatchAction
{
    public $name = 'Gộp đơn hàng chưa xuất kho';
    protected $selector = '.merge-order';

    /**
     * {@inheritdoc}
     */
    public function actionScript()
    {
        $warning = __('Vui lòng chọn từ 2 đơn hàng trở lên');

        return <<<SCRIPT
        var key = $.admin.grid.selected();
        
        if (key.length < 2) {
            $.admin.toastr.error('{$warning}', '', {positionClass: 'toast-top-center'});
            return ;
        }
        
        Object.assign(data, {_key:key});
SCRIPT;
    }
    
    public function handle(Collection $collection, Request $request)
    {
        $orderService = new OrderService();

        $transport_code_ids = [];
        $customer_name = [];
        
        foreach ($collection as $model) {
            $name = $model->transportCode->pluck('customer_code_input')->toArray();
            $customer_name = array_merge($customer_name, $name);

            $transportCode = $model->transportCode->pluck('id')->toArray();
            $transport_code_ids = array_merge($transport_code_ids, $transportCode);
        }

        $customer_name = array_unique($customer_name);

        if (sizeof($customer_name) > 1) {
            return $this->response()->error('Các đơn đã chọn có mã khách hàng khác nhau.');
        }

        $ids_route = implode(',', $transport_code_ids);
        $route = route('admin.payments.index', ['ids' => $ids_route]) 
        . "?type=payment_not_export&mode=merge_order&payment_customer_id=3159";

        return $this->response()->redirect($route);
    }

    public function form()
    {
        $this->textarea('noti', 'Thông báo')->default('Các mã vận đơn sẽ được gộp vào thanh toán lại trong 1 đơn chưa xuất kho mới. Các đơn cũ chuyển sang trạng thái huỷ. Kiểm tra kỹ trước khi thao tác.')->disable();
    }

    public function html()
    {
        return "<a class='merge-order btn btn-sm btn-danger'><i class='fa fa-check'></i>&nbsp; ".$this->name."</a>";
    }

}