<?php

namespace App\Admin\Controllers\System;

use App\Models\System\ExchangeRate;
use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ExchangeRateController extends AdminController
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
        $grid = new Grid(new ExchangeRate());

        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT');
        $grid->vnd()->style('text-align: center');
        $grid->rmb()->style('text-align: center');
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
        $form = new Form(new ExchangeRate);

        $form->text('rmb')->rules(['required']);
        $form->text('vnd')->rules(['required']);
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });

        return $form;
    }
}
