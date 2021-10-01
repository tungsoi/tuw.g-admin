<?php

namespace App\Admin\Controllers\Report;

use App\Admin\Services\UserService;
use App\Models\System\Transaction;
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
        $grid->model()->select('symbol_name', 'id', 'wallet')->whereIsCustomer(1);

        $grid->filter(function($filter) {
            $filter->expand();
            $filter->disableIdFilter();
            $filter->like('symbol_name');
        });

        // $service = new UserService();
        // $customers = $service->GetListCustomer();
        // $customer_ids = array_keys($customers->toArray());

        
        $transaction_customer_ids = Transaction::select('customer_id')->groupBy('customer_id')->pluck('customer_id');
        $grid->model()->whereIn('id', $transaction_customer_ids);
        // dd($transaction_customer_ids->toArray());
        // $customers = User::whereIn('id', $transaction_customer_ids->toArray())->get();

        // $ids = [];
        // foreach ($customers as $customer) {
        //     $wallet = $customer->wallet;

        //     $service = new UserService();

        //     $data = $service->GetCustomerTransactionHistory($customer->id, false);
        //     $lastMoney = $data[0]['after_payment'];

        //     if ($lastMoney != $wallet) {
        //         $ids[] = $customer->id;
        //     }
        // }
        // dd($ids);
        // $temp = [];
        // foreach ($customer_ids as $customer_id) {
            
        // }
        // dd(array_diff($customer_ids, $transaction_customer_ids->toArray()));
            $grid->symbol_name('Mã khách hàng');
            $grid->wallet('Ví tiền');

            $grid->id('Tiền ví theo giao dịch')->display(function () {

                $id = $this->id;
                Admin::script(
                <<<EOT
                $( document ).ready(function() {
                    $.ajax({
                        url: "customers/" + $id + "/calculator_wallet",
                        type: 'GET',
                        dataType: "JSON",
                        success: function (response)
                        {
                            if (response.status) {
                                $('#calculator-wallet-{$id}').html(response.message);

                                if (! response.flag) {
                                    $('#calculator-wallet-{$id}').css('color', 'red');
                                } else {
                                    $('#calculator-wallet-{$id}').css('color', 'green');
                                }
                            }
                        }
                    });
                }); 
EOT
);
                return "<span id='calculator-wallet-$id'></span>";
            });
        // $grid->symbol_name('Mã khách hàng')->display(function () {
        //     return "<a href='https://aloorder.vn/admin/customers/".$this->id."/recharge-history' target='_blank'>".$this->symbol_name."</a>";
        // });
        // $grid->wallet('Tiền trong ví')->display(function () {
        //     return round($this->wallet);
        // });
        // $grid->wallet_payment('Tiền tính theo lịch sử giao dịch')->display(function () use ($service) {
        //     $payment = $service->GetCustomerTransactionHistory($this->id, false);

        //     if (is_array($payment) && sizeof($payment) > 0) {
        //         return $payment[0]['after_payment'];
        //     }
        //     // $payment = round($this->totalRecharge($this->id));
        //     // if ($payment != round($this->wallet)) {
        //     //     return "<span class='label label-danger'>".$payment."</span>";
        //     // }

        //     // return $payment;

        //     return null;
        // });

        $grid->paginate(200);

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

        return $grid;
    }
}