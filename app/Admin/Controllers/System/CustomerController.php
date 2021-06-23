<?php

namespace App\Admin\Controllers\System;

use App\Admin\Actions\Customer\PurchaseOrder;
use App\Admin\Actions\Customer\Recharge;
use App\Admin\Actions\Customer\TransportOrder;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Hash;
use Encore\Admin\Controllers\AdminController;
use App\User;
use Illuminate\Support\Str;

class CustomerController extends AdminController
{
    /**
     * {@inheritdoc}
     */
    protected function title()
    {
        return 'Danh sách khách hàng';
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User());
        $grid->model()->whereIsCustomer(User::CUSTOMER)->orderBy('id', 'desc');

        $grid->filter(function($filter) {
            $filter->disableIdFilter();

            $filter->column(1/2, function ($filter) {
                $filter->like('name', 'Họ và tên');
            });
            $filter->column(1/2, function ($filter) {
                $filter->like('username', 'Email');
            });
        });

        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT');
        $grid->avatar('Ảnh đại diện')->lightbox(['width' => 30, 'height' => 30])->style('text-align: center; width: 100px;');
        $grid->symbol_name('Mã khách hàng')->style('text-align: center;');
        $grid->column('username', "Email");
        $grid->column('phone_number', "Số điện thoại")->style('text-align: center;');
        $grid->warehouse()->name('Kho nhận hàng')->style('text-align: center;');
        $grid->wallet('Ví tiền')->display(function () {
            return number_format($this->wallet);
        });
        $grid->address('Địa chỉ')->width(200);
        $grid->total_weight('Tổng cân');
        $grid->last_transaction('Giao dịch gần nhất');
        $grid->note('Ghi chú');

        $states = [
            'on'  => ['value' => User::ACTIVE, 'text' => 'Mở', 'color' => 'success'],
            'off' => ['value' => User::DEACTIVE, 'text' => 'Khoá', 'color' => 'danger'],
        ];
        $grid->column('is_active', 'Trạng thái đăng nhập')->switch($states)->style('text-align: center');
        $grid->column('created_at', 'Ngày tạo tài khoản')->display(function () {
            return date('H:i | d-m-Y', strtotime($this->created_at));
        })->style('text-align: center');

        $grid->disableCreateButton();
        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        // $grid->disableExport();
        $grid->paginate(20);
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableView();    
            $actions->append(new PurchaseOrder($actions->getKey()));
            $actions->append(new TransportOrder($actions->getKey()));
            $actions->append(new Recharge($actions->getKey()));

        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(User::findOrFail($id));
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form()
    {
        $permissionModel = config('admin.database.permissions_model');
        $roleModel = config('admin.database.roles_model');

        $form = new Form(new User());

        $userTable = config('admin.database.users_table');
        $connection = config('admin.database.connection');

        $form->image('avatar', trans('admin.avatar'));
        $form->text('username', trans('admin.username'))
            ->creationRules(['required', "unique:{$connection}.{$userTable}"])
            ->updateRules(['required', "unique:{$connection}.{$userTable},username,{{id}}"]);

        $form->text('name', trans('admin.name'))->rules('required');
        $form->divider();
        $form->password('password', trans('admin.password'))->rules('required|confirmed');
        $form->password('password_confirmation', trans('admin.password_confirmation'))->rules('required')
            ->default(function ($form) {
                return $form->model()->password;
            });

        $form->ignore(['password_confirmation']);

        $form->divider();
        $form->multipleSelect('roles', trans('admin.roles'))->options($roleModel::all()->pluck('name', 'id'));
        $form->multipleSelect('permissions', trans('admin.permissions'))->options($permissionModel::all()->pluck('name', 'id'));
        $form->hidden('is_customer')->default(User::ADMIN);
        $states = [
            'off' => ['value' => User::DEACTIVE, 'text' => 'Đã nghỉ', 'color' => 'danger'],
            'on'  => ['value' => User::ACTIVE, 'text' => 'Làm việc', 'color' => 'success']
        ];
        $form->switch('is_active', 'Trạng thái')->states($states)->default(1);

        $form->saving(function (Form $form) {
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = Hash::make($form->password);
            }
        });

        $form->disableEditingCheck();
        $form->disableCreatingCheck();
        $form->disableViewCheck();
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });

        return $form;
    }
}
