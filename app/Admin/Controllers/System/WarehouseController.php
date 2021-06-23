<?php

namespace App\Admin\Controllers\System;

use App\Models\System\Warehouse;
use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class WarehouseController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Kho hàng';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Warehouse());

        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT');
        $grid->name('Tên kho hàng')->style('text-align: center');
        $grid->code('Mã kho hàng')->style('text-align: center');
        $grid->address('Địa chỉ')->style('text-align: center');
        $states = [
            'off' => ['value' => Warehouse::CLOSE, 'text' =>  Warehouse::CLOSE_TXT, 'color' => 'danger'],
            'on'  => ['value' => Warehouse::LIVE, 'text' => Warehouse::LIVE_TXT, 'color' => 'success']
        ];
        $grid->column('is_active', 'Tình trạng kho')->switch($states)->style('text-align: center');
        $grid->column('created_at', "Ngày tạo")->display(function () {
            return date('H:i | d-m-Y', strtotime($this->created_at));
        })->style('text-align: center');
        $grid->userLead()->name('Nhân viên phụ trách')->style('text-align: center');
        $grid->disableExport();
        $grid->disableFilter();
        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->disablePagination();
        $grid->disablePerPageSelector();
        $grid->paginate(100);
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
        });

        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Warehouse);

        $form->text('name', 'Tên kho hàng')->rules(['required']);
        $form->text('code', 'Mã kho hàng')->help('Sử dụng gắn vào mã khách hàng theo từng kho')->rules(['required']);
        $form->text('address', 'Địa chỉ')->rules(['required']);
        $states = [
            'off' => ['value' => Warehouse::CLOSE, 'text' =>  Warehouse::CLOSE_TXT, 'color' => 'danger'],
            'on'  => ['value' => Warehouse::LIVE, 'text' => Warehouse::LIVE_TXT, 'color' => 'success']
        ];
        $form->switch('is_active', 'Trạng thái')->states($states)->default(1);
        $form->select('user_id', 'Nhân viên phụ trách')->options(
            User::whereIsCustomer(User::ADMIN)->whereIsActive(User::ACTIVE)->pluck('name', 'id')
        )->rules(['required']);
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });

        return $form;
    }
}
