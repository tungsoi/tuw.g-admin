<?php

namespace App\Admin\Controllers\CustomerReport;

use App\Admin\Services\UserService;
use App\Models\System\Alert;
use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TransportReportController extends AdminController
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
    protected $title = 'Thống kê sản lượng vận chuyển khách hàng';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User());
        $grid->model()->whereIsCustomer(User::CUSTOMER)->with('paymentOrders');

        if (isset($_GET['type_wallet']) && $_GET['type_wallet'] == 0) {
            $grid->model()->orderByRaw('CONVERT(wallet, SIGNED) desc');
        } else {
            $grid->model()->orderByRaw('CONVERT(wallet, SIGNED) asc');
        }

        $grid->expandFilter();
        $grid->filter(function($filter) {
            $filter->disableIdFilter();
            $filter->equal('id', 'Mã khách hàng')->select($this->userService->GetListCustomer());
            $filter->between('created_at', 'Thời gian')->date();
        });

        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT');
        $grid->symbol_name('Mã khách hàng');
        $grid->wallet('Ví tiền')->display(function () {
            $label = $this->wallet < 0 ? "red" : "green";
            return "<span style='color: {$label}'>".number_format($this->wallet)."</span>";
        })->style('text-align: right; max-width: 150px;');
        $grid->column('transport_count', 'Số lượng đơn')->display(function () {
            return $this->paymentOrders->count();
        });
        $grid->column('amount', 'Tổng doanh thu (VND)')->display(function () {
            return number_format($this->paymentOrders->sum('amount'));
        });
        $grid->column('total_kg', 'Tổng cân (Kg)')->display(function () {
            return number_format($this->paymentOrders->sum('total_kg'));
        });
        $grid->column('total_m3', 'Tổng khối (M3)')->display(function () {
            return number_format($this->paymentOrders->sum('total_m3'));
        });
        $grid->column('total_advance_drag', 'Tổng ứng kéo (Tệ)')->display(function () {
            return number_format($this->paymentOrders->sum('total_advance_drag'));
        });

        $grid->disableActions();
        $grid->disableBatchActions();
        $grid->paginate(100);

        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Alert);

        $form->text('title', "Tiêu đề")->rules(['required']);
        $form->summernote('content', "Nội dung")->rules(['required']);
        $isDefault = [
            'off' => ['value' => 0, 'text' =>  'Tắt', 'color' => 'danger'],
            'on'  => ['value' => 1, 'text' => 'Mở', 'color' => 'success']
        ];
        $form->switch('status', 'Loại kho')->states($isDefault)->default(1);
        $form->hidden('created_user_id')->default(Admin::user()->id);
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });

        return $form;
    }
}
