<?php

namespace App\Admin\Controllers\System;

use App\Admin\Actions\Core\BtnDelete;
use App\Admin\Services\UserService;
use App\Models\System\Transaction;
use App\Models\System\TransactionType;
use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;

class TransactionController extends AdminController
{
    protected $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Lịch sử giao dịch';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Transaction());
        $grid->model()->where('money', '!=', 0)->orderBy('created_at', 'desc');

        if ($this->userService->isFilter()) {
            $grid->expandFilter();
        }

        // if (isset($_GET['user_id_created']) && $_GET['user_id_created'] != "") {
        //     dd($_GET['user_id_created']);
        // }

        $grid->expandFilter();
        $grid->filter(function($filter) {
            $filter->disableIdFilter();

            $filter->column(1/3, function ($filter) {
                $filter->like('content', 'Nội dung');
                $ar = $this->userService->GetListArEmployee();
                $wh =  $this->userService->GetListWarehouseEmployee();

                $fil =$ar->toArray() + $wh->toArray();

                $filter->in('user_id_created', 'Người thực hiện')->multipleSelect($fil);
            });
            $filter->column(1/3, function ($filter) {
                $filter->in('customer_id', 'Mã khách hàng')->select($this->userService->GetListCustomer());
                $filter->in('type_recharge', 'Loại giao dịch')->multipleSelect(TransactionType::pluck('name', 'id'));
            });
            $filter->column(1/3, function ($filter) {
                $filter->between('created_at', 'Ngày tạo')->date();
            });

            Admin::style('
                #filter-box label {
                    padding: 0px !important;
                    padding-top: 10px;
                    font-weight: 600;
                    font-size: 12px;
                }
                #filter-box .col-sm-2 {
                    width: 100% !important;
                    text-align: left;
                    padding: 0px 15px 3px 15px !important;
                }
                #filter-box .col-sm-8 {
                    width: 100% !important;
                }
            ');
        });

        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT');
        $grid->id('_id');
        $grid->column('created_at', "Ngày tạo")->display(function () {
            return date('H:i | d-m-Y', strtotime($this->created_at));
        })->style('text-align: center; width: 200px');
        $grid->userCreated()->name('Người thực hiện');

        $grid->column('updated_at', "Ngày cập nhật")->display(function () {
            return date('H:i | d-m-Y', strtotime($this->updated_at));
        })->style('text-align: center; width: 200px');
        $grid->userUpdated()->name('Người chỉnh sửa');
        $grid->customer()->symbol_name('Mã khách hàng');
        $grid->content('Nội dung giao dịch');
        $grid->type()->name('Chi tiết giao dịch');
        $grid->money('Số tiền')->display(function () {
            return number_format($this->money);
        })->totalRow(function ($amount) {
            return number_format($amount);
        });
        $grid->type_detail('Loại giao dịch')->display(function () {
            if (in_array($this->type_recharge, [0, 1, 2])) {
                $label = "success";
                $text = "Cộng tiền";
            } else {
                $label = "danger";
                $text = "Trừ tiền";
            }

            return "<span class='label label-{$label}'>".$text."</span>";
        });
        $grid->disableExport();
        $grid->disableCreateButton();
        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableEdit();
            $actions->disableDelete();

            $route = route('admin.customers.transactions', $actions->row->customer_id) . "?mode=recharge&transaction_id=" . $actions->getKey();
            $actions->append('
                <a href="'.$route.'" class="grid-row-edit btn btn-xs btn-warning" data-toggle="tooltip" title="" data-original-title="Chỉnh sửa">
                    <i class="fa fa-edit"></i>
                </a>
            ');

            $urlDelete = route('admin.transactions.destroy', $actions->getKey());
            $actions->append(new BtnDelete($actions->getKey(), $urlDelete));
        });

        Admin::script(
        <<<EOT
        
        $('tfoot').each(function () {
            $(this).insertAfter($(this).siblings('thead'));
        });
EOT);

        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Transaction);

        $form->select('customer_id', 'Mã khách hàng')->options($this->userService->GetListCustomer())->rules(['required']);
        $form->select('type_recharge', 'Loại giao dịch')->options(TransactionType::pluck('name', 'id'))->rules(['required']);
        $form->currency('money', 'Số tiền')->digits(0)->symbol('VND')->rules(['required']);
        $form->text('content', 'Nội dung')->rules(['required']);
        $form->hidden('updated_user_id')->default(Admin::user()->id);

        $form->html('Khi chỉnh sửa giao dịch, hệ thống sẽ lưu lại ID người chỉnh sửa và mất 1 thời gian để update lại tiền ví của khách hàng.');
        
        $form->confirm('Bạn có chắc chắn muốn chỉnh sửa giao dịch này ?');
        
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });

        return $form;
    }

    protected function detail() {
        $grid = new Grid(new Transaction());

        $grid->setTitle('Giao dịch Duplicate');

        $referrals =  Transaction::select('content')->selectRaw('count(content)')
        ->groupBy('content')
        ->where('type_recharge', 3)
        ->where('content', 'like', 'Thanh toán%')
        ->orWhere('content', 'like', 'Đặt cọc%')
        ->having(DB::raw('count(content)'), '>', 1)
        ->pluck('content');

        $grid->model()->where('money', '!=', 0)
        ->whereIn('content', $referrals)
        ->orderBy('id', 'desc');

        $grid->disableFilter();

        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT');
        $grid->id('_id');
        $grid->column('created_at', "Ngày tạo")->display(function () {
            return date('H:i | d-m-Y', strtotime($this->created_at));
        })->style('text-align: center; width: 200px');
        $grid->userCreated()->name('Người thực hiện');
        $grid->customer()->symbol_name('Mã khách hàng');
        $grid->content('Nội dung giao dịch');
        $grid->type()->name('Chi tiết giao dịch');
        $grid->money('Số tiền')->display(function () {
            return number_format($this->money);
        });
        $grid->type_detail('Loại giao dịch')->display(function () {
            if (in_array($this->type_recharge, [0, 1, 2])) {
                $label = "success";
                $text = "Cộng tiền";
            } else {
                $label = "danger";
                $text = "Trừ tiền";
            }

            return "<span class='label label-{$label}'>".$text."</span>";
        });
        $grid->disableExport();
        $grid->disableCreateButton();
        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
            $actions->disableEdit();
            $actions->disableDelete();

            // $route = route('admin.customers.transactions', $actions->row->customer_id) . "?mode=recharge&transaction_id=" . $actions->getKey();
            // $actions->append('
            //     <a href="'.$route.'" class="grid-row-edit btn btn-xs btn-warning" data-toggle="tooltip" title="" data-original-title="Chỉnh sửa">
            //         <i class="fa fa-edit"></i>
            //     </a>
            // ');

            $urlDelete = route('admin.transactions.destroy', $actions->getKey());
            $actions->append(new BtnDelete($actions->getKey(), $urlDelete));
        });
        $grid->paginate(500);

        return $grid;
    }

    public function destroy($id)
    {
        $transaction = Transaction::find($id);
        $customer = User::find($transaction->customer_id);

        $transaction->delete();
        $customer->updateWalletByHistory();

        admin_toastr('Xoá thành công', 'success');

        return response()->json([
            'status'    =>  'success',
            'message'   =>  'Xoá thành công',
            'isRedirect'    =>  true,
            'url'   =>  route('admin.transactions.duplicate')
        ]);
    }
}
