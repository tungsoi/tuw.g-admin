<?php

namespace App\Admin\Controllers;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Hash;
use Encore\Admin\Controllers\AdminController;
use App\User;

class UserController extends AdminController
{
    /**
     * {@inheritdoc}
     */
    protected function title()
    {
        return 'Quản trị viên';
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User());
        $grid->model()->whereIsCustomer(User::ADMIN)->orderBy('id', 'desc');

        $grid->expandFilter();
        $grid->filter(function($filter) {
            $filter->disableIdFilter();
            $filter->column(1/3, function ($filter) {
                $filter->like('username', 'Tên đăng nhập');
            });
        });

        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT');
        $grid->avatar('Ảnh đại diện')->lightbox(['width' => 50, 'height' => 50]);
        $grid->column('username', trans('admin.username'));
        $grid->column('name', trans('admin.name'));
        $grid->column('roles', trans('admin.roles'))->pluck('name')->label();

        $states = [
            'on'  => ['value' => User::ACTIVE, 'text' => 'Mở', 'color' => 'success'],
            'off' => ['value' => User::DEACTIVE, 'text' => 'Khoá', 'color' => 'danger'],
        ];
        $grid->column('status', 'Trạng thái đăng nhập')->switch($states);
        $grid->column('created_at', trans('admin.created_at'));

        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->paginate(100);
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableDelete();
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
            'off' => ['value' => User::DEACTIVE, 'text' => 'Khoá tài khoản', 'color' => 'danger'],
            'on'  => ['value' => User::ACTIVE, 'text' => 'Mở tài khoản', 'color' => 'success']
        ];
        $form->switch('status', 'Trạng thái')->states($states)->default(1);

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
