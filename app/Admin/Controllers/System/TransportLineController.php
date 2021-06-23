<?php

namespace App\Admin\Controllers\System;

use App\Models\System\TransportLine;
use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TransportLineController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Tỷ giá';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new TransportLine());

        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT');
        $grid->name('Tên đường vận chuyển')->style('text-align: center');
        $grid->code('Mã viết tắt')->style('text-align: center');
        $grid->column('created_at', "Ngày tạo")->display(function () {
            return date('H:i | d-m-Y', strtotime($this->created_at));
        })->style('text-align: center; width: 200px');

        $grid->column('updated_at', "Ngày cập nhật")->display(function () {
            return date('H:i | d-m-Y', strtotime($this->updated_at));
        })->style('text-align: center; width: 200px');
        
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
        $form = new Form(new TransportLine);

        $form->text('name', "Tên đường vận chuyển")->rules(['required']);
        $form->text('code', "Mã viết tắt")->rules(['required']);
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });

        return $form;
    }
}
