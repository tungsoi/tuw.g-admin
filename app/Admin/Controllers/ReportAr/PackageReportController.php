<?php

namespace App\Admin\Controllers\ReportAr;

use App\Models\ArReport\Unit;
use App\Models\ReportWarehouse\ReportWarehousePortal;
use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class PackageReportController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Hạch toán theo mã lô';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ReportWarehousePortal());
        $grid->model()->orderBy('id', 'desc');

        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT');
        $grid->title('Mã lô');
        $grid->column('amount_kg', 'Tổng tiền thu khách theo KG')->display(function () {
            $transport_codes = $this->transportCode->count();

            return $transport_codes;
        });
        $grid->column('amount_m3', 'Tổng tiền thu khách theo Khối');
        $grid->column('amount', 'Tổng tiền đầu ra');
        $grid->column('amount', 'Số lượng đầu vào');
        $grid->column('amount', 'Đơn vị');
        $grid->column('amount', 'Đơn giá');
        $grid->column('amount', 'Tổng tiền đầu vào');
        $grid->column('amount', 'Lãi/Lỗ');
        
        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->disableFilter();
        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->paginate(20);
        $grid->disableActions();
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
        $form = new Form(new Unit);

        $form->text('title', "Tiêu đề")->rules(['required']);

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });

        return $form;
    }
}
