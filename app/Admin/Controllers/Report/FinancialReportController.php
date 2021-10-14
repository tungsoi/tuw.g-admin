<?php

namespace App\Admin\Controllers\Report;

use App\Models\FinancialReport\Portal;
use App\Models\FinancialReport\Detail;
use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class FinancialReportController extends AdminController
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
        $grid = new Grid(new Portal());

        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT');
        
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
        $form = new Form(new Detail);
        $form->setTitle("BÁO CÁO KẾT QUẢ HOẠT ĐỘNG KINH DOANH TRONG THÁNG 8/2021");
        $form->html(
            view('admin.system.financial_reports.detail')->render()
        );
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });
        $form->disableSubmit();
        $form->disableReset();

        Admin::style('
            form .col-sm-2, form .col-sm-8 {
                width: 100%;
                text-align: left !important;
                padding: 0px !important;
            }
            .box {
                border: none !important;
            }
            .box-footer .col-md-2 {
                display: none;
            }
            .box-footer .col-md-8 {
                padding-left: 0px !important;
            }
            .box-footer {
                padding-top: 0px;
                padding-bottom: 0px;
                border: none;
            }
            .col-md* {
                padding: 0px !important;
            }
        ');

        return $form;
    }
}
