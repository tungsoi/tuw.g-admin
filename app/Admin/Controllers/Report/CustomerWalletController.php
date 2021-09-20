<?php

namespace App\Admin\Controllers\Report;

use App\Admin\Services\UserService;
use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;

class CustomerWalletController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title;

    public function __construct()
    {
        $this->title = 'Đối soát tiền ví khách hàng';
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $service = new UserService();

        $grid = new Grid(new User());
        $grid->model()->whereIsCustomer(1);

        $grid->filter(function($filter) {
            $filter->expand();
            $filter->disableIdFilter();
            $filter->like('symbol_name');
        });

        $grid->symbol_name('Mã khách hàng')->display(function () {
            return "<a href='https://aloorder.vn/admin/customers/".$this->id."/recharge-history' target='_blank'>".$this->symbol_name."</a>";
        });
        $grid->wallet('Tiền trong ví')->display(function () {
            return round($this->wallet);
        });
        $grid->wallet_payment('Tiền tính theo lịch sử giao dịch')->display(function () use ($service) {
            $payment = $service->GetCustomerTransactionHistory($this->id, false);

            if (is_array($payment) && sizeof($payment) > 0) {
                return $payment[0]['after_payment'];
            }
            // $payment = round($this->totalRecharge($this->id));
            // if ($payment != round($this->wallet)) {
            //     return "<span class='label label-danger'>".$payment."</span>";
            // }

            // return $payment;

            return null;
        });

        $grid->paginate(1);

        $grid->setActionClass(\Encore\Admin\Grid\Displayers\Actions::class);
        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableDelete();
            $actions->disableEdit();

            // if ($this->row->wallet != User::totalRecharge($this->row->id)) {
            //     $actions->append('
            //         <a class="grid-row-edit btn btn-sm btn-success btn-sync-wallet" data-id="'.$this->getKey().'">
            //             Làm chuẩn 
            //         </a>
            //     ');
            // }
           
        });

        Admin::script(
            <<<EOT
            
            $(document).on('click', '.btn-sync-wallet', function () {
                let t_this = $(this);
                let t_log = $(this).parent().prev();
                let t_wallet = $(this).parent().prev().prev();
  
                $.ajax({
                    type: 'POST',
                    url: '/api/sync-wallet-customer',
                    data: {
                        id: $(this).data('id')
                    },
                    success: function(response) {
                        if (response.error == false) {
                            toastr.success('Lưu thành công.');
                            t_log.html(response.wallet);
                            t_wallet.html(response.wallet);   
                            t_this.remove();
                        }
                    }
                });
            });
EOT
    );

        return $grid;
    }
}