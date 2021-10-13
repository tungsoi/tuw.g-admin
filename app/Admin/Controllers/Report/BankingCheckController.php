<?php

namespace App\Admin\Controllers\Report;

use App\Admin\Services\UserService;
use App\Models\System\Transaction;
use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;

class BankingCheckController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title;

    public function __construct()
    {
        $this->title = 'Báo cáo nạp tiền tài khoản';
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $days = Transaction::select('created_at')
            ->whereNotNull('bank_id')
            ->whereTypeRecharge(2)
            ->groupBy('')

        $grid = new Grid(new User());
        $grid->model()->select('symbol_name', 'id', 'wallet')->whereIsCustomer(1);

        $grid->filter(function($filter) {
            $filter->expand();
            $filter->disableIdFilter();
            $filter->like('symbol_name');
        });

        $transaction_customer_ids = Transaction::select('customer_id')->groupBy('customer_id')->pluck('customer_id');
        $grid->model()->whereIn('id', $transaction_customer_ids);

            $grid->symbol_name('Mã khách hàng');
            $grid->wallet('Ví tiền')->display(function () {
                return number_format($this->wallet, 0, '.', '');
            });

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
                                    $('#calculator-wallet-{$id}').parent().parent().remove();
                                }
                            }
                        }
                    });
                }); 
EOT
);
                return "<span id='calculator-wallet-$id'></span>";
            });

        $grid->paginate(200);

        $grid->setActionClass(\Encore\Admin\Grid\Displayers\Actions::class);
        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableDelete();
            $actions->disableEdit();

                $actions->append(
                    '<a class="btn-sync-wallet btn btn-xs btn-danger" data-toggle="tooltip" title="Làm chuẩn" data-key="'.$this->row->id.'">
                <i class="fa fa-check"></i>
            </a>'
                );
        });

        Admin::script(
            <<<EOT
            $( document ).ready(function() {
                $('.btn-sync-wallet').on('click', function () {
                    let id = $(this).data('key');

                    $.admin.toastr.success(id, '', {timeOut: 500});

                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $.ajax({
                        url: "customers/update_wallet",
                        type: 'POST',
                        dataType: "JSON",
                        data: {
                            id: id
                        },
                        success: function (response)
                        {
                            $.admin.toastr.success(id, '', {timeOut: 500});
                        }
                    });
                });
            }); 
EOT
);

        return $grid;
    }
}