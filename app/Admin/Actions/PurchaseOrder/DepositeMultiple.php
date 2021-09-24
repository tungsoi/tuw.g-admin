<?php

namespace App\Admin\Actions\PurchaseOrder;

use App\Admin\Services\OrderService;
use App\Admin\Services\UserService;
use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class DepositeMultiple extends BatchAction
{
    public $name = 'Đặt cọc tiền hàng loạt';
    protected $selector = '.deposite-multiple';

    /**
     * {@inheritdoc}
     */
    public function actionScript()
    {
        $warning = __('Vui lòng chọn Đơn hàng');

        return <<<SCRIPT
        var key = $.admin.grid.selected();
        
        if (key.length === 0) {
            $.admin.toastr.error('{$warning}', '', {positionClass: 'toast-top-center'});
            return ;
        }
        
        let rs = Object.assign(data, {_key:key});

        $('input[name="ids_deposite_multiple"]').val(rs._key);
        console.log(rs._key, "resful");
SCRIPT;
    }

    public function handle(Collection $collection, Request $request)
    {   
        //
    }

    public function form()
    {
        $this->text('noti', 'Thông báo')->default('Xác nhận đặt cọc tất cả đơn đã chọn ?')->disable();

        $data = [
            '10'    =>  "10%",
            '20'    =>  "20%",
            '30'    =>  "30%",
            '40'    =>  "40%",
            '50'    =>  "50%",
            '60'    =>  "60%",
            '70'    =>  "70%",
            '80'    =>  "80%",
            '90'    =>  "90%",
            '100'    =>  "100%",
        ];
        $this->select('percent_deposite', 'Tỉ lệ % cọc')->options($data)->default(70);
        $this->hidden('ids_deposite_multiple');

        Admin::script(
            <<<EOT
            let modal_ele = $('#app-admin-actions-purchaseorder-depositemultiple');
            modal_ele.find('.btn-primary').attr('type', 'button');
            modal_ele.find('.btn-primary').attr('id', 'btn-submit-deposite-multiple');

            $(document).on('click', '#btn-submit-deposite-multiple', function () {

                $('.loading-overlay').toggle();
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    url: "purchase_orders/post_admin_deposite_multiple",
                    type: 'POST',
                    dataType: "JSON",
                    data: {
                        percent: modal_ele.find('select[name="percent_deposite"]').val(),
                        ids: modal_ele.find('input[name="ids_deposite_multiple"]').val()
                    },
                    success: function (response)
                    {
                        if (response.status) {
                            $('.loading-overlay').toggle();

                            modal_ele.find('.modal-body').append("<span style='color: green; font-size: 14px; font-weight: bold;' id='notify-deposite-multiple'>"+response.message+"</span>");

                            $.admin.toastr.warning("Page tự động reload sau 10s", '', {positionClass: 'toast-top-center'});

                            setTimeout(function () {
                                modal_ele.find('.modal-footer').remove();
                            }, 1000);

                            setTimeout(function () {
                                location.reload();
                            }, 10000);
                        } else {

                            $('.loading-overlay').hide();
                            $.admin.toastr.error(response.message, '', {positionClass: 'toast-top-center'});
                        }

                    }
                });
            });
EOT);
    }

    public function html()
    {
        return "<a class='deposite-multiple btn btn-sm btn-danger'>Đặt cọc tất cả</a>";
    }

}