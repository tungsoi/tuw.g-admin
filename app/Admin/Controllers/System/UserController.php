<?php

namespace App\Admin\Controllers\System;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Hash;
use Encore\Admin\Controllers\AdminController;
use App\User;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\Str;

class UserController extends AdminController
{
    /**
     * {@inheritdoc}
     */
    protected function title()
    {
        return 'Danh sách nhân viên';
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User());
        $grid->model()->select('id', 'avatar', 'username', 'name', 'is_active', 'created_at', 'phone_number');
        $grid->model()->whereIsCustomer(User::ADMIN)->orderBy('id', 'desc');

        $grid->expandFilter();
        $grid->filter(function($filter) {
            $filter->disableIdFilter();

            $filter->column(1/4, function ($filter) {
                $filter->like('name', 'Họ và tên');
            });
            $filter->column(1/4, function ($filter) {
                $filter->like('username', 'Email');
            });
            $filter->column(1/4, function ($filter) {
                $filter->like('phone_number', 'Số điện thoại');
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
        $grid->avatar('Ảnh đại diện')->lightbox(['width' => 30, 'height' => 30])->style('text-align: center');
        $grid->column('name', 'Họ và tên');
        $grid->column('username', 'Tên đăng nhập / Email');
        $grid->column('phone_number', "Số điện thoại");
        $grid->column('roles', trans('admin.roles'))->pluck('name')->label()->width(200);

        $states = [
            'on'  => ['value' => User::ACTIVE, 'text' => 'Làm việc', 'color' => 'success'],
            'off' => ['value' => User::DEACTIVE, 'text' => 'Đã nghỉ', 'color' => 'danger'],
        ];
        $grid->column('is_active', 'Trạng thái đăng nhập')->switch($states)->style('text-align: center');
        $grid->column('created_at', 'Ngày tạo tài khoản')->display(function () {
            return date('H:i | d-m-Y', strtotime($this->created_at));
        })->style('text-align: center');

        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->disableExport();
        $grid->paginate(20);
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            // $actions->disableDelete();
            $actions->disableView();
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
