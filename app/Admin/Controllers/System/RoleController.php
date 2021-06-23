<?php

namespace App\Admin\Controllers\System;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Controllers\AdminController;

class RoleController extends AdminController
{
    /**
     * {@inheritdoc}
     */
    protected function title()
    {
        return trans('admin.roles');
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $roleModel = config('admin.database.roles_model');

        $grid = new Grid(new $roleModel());
        $grid->expandFilter();
        $grid->filter(function($filter) {
            $filter->disableIdFilter();

            $filter->column(1/2, function ($filter) {
                $filter->like('name', 'Họ và tên');
            });
            $filter->column(1/2, function ($filter) {
                $filter->like('slug', 'Email');
            });
        });
        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT');
        $grid->column('slug', "Mã vai trò");
        $grid->column('name', "Tên vai trò");

        $grid->column('permissions', trans('admin.permission'))->pluck('name')->label()->width(500);

        $grid->column('created_at', trans('admin.created_at'))->display(function () {
            return date('H:i | d-m-Y', strtotime($this->created_at));
        })->style('text-align: center');
        $grid->column('updated_at', trans('admin.updated_at'))->display(function () {
            return date('H:i | d-m-Y', strtotime($this->created_at));
        })->style('text-align: center');

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableDelete();
            $actions->disableView();
        });

        $grid->disableBatchActions();

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
        $roleModel = config('admin.database.roles_model');

        $show = new Show($roleModel::findOrFail($id));

        $show->field('id', 'ID');
        $show->field('slug', trans('admin.slug'));
        $show->field('name', trans('admin.name'));
        $show->field('permissions', trans('admin.permissions'))->as(function ($permission) {
            return $permission->pluck('name');
        })->label();
        $show->field('created_at', trans('admin.created_at'));
        $show->field('updated_at', trans('admin.updated_at'));

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

        $form = new Form(new $roleModel());

        $form->display('id', 'ID');

        $form->text('slug', trans('admin.slug'))->rules('required');
        $form->text('name', trans('admin.name'))->rules('required');
        $form->listbox('permissions', trans('admin.permissions'))->options($permissionModel::all()->pluck('name', 'id'));

        $form->display('created_at', trans('admin.created_at'));
        $form->display('updated_at', trans('admin.updated_at'));

        return $form;
    }
}
