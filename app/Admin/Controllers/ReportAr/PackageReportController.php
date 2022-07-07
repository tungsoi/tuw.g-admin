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
        $grid->model()->orderBy('id', 'desc')->with('transportCode');

        $grid->filter(function($filter) {
            $filter->expand();
            $filter->disableIdFilter();
            $filter->like('title', "Ký hiệu");
        });

        $grid->header(function () {
            $html = "<h4>VD: KG 25, 25.5, ....</h4>";
            $html .= "<h4>VD: M3 5.345, ... </h4>";

            return $html;
        });

        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT');
        $grid->title('Mã lô');

        $grid->column('output', 'Đầu ra')->display(function () {
            $amount_output = $this->amount_output();

            return view('admin.system.package_report',compact('amount_output'));
        });
        $grid->column('input_count', 'Số lượng đầu vào')->editable();
        $grid->column('input_price', 'Đơn giá')->editable();
        $grid->column('input_type', 'Đơn vị')->editable('select', [
            1 => 'kg',
            2   =>  'm3'
        ]);
        $grid->column('amount_input', 'Tổng tiền đầu vào')->display(function () {
            return number_format($this->amount_input());
        });
        $grid->column('revenue', 'Lãi/Lỗ')->display(function () {
            $amount_output = $this->amount_output()['amount'];
            $amount_input =  $this->amount_input();

            return number_format($amount_output - $amount_input);
        });
        
        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->paginate(10);
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
        $form = new Form(new ReportWarehousePortal);

        $form->text('input_count', "Số lượng đầu vào");
        $form->select('input_type', "Đơn vị")->options([
            'kg',
            'm3'
        ]);
        $form->text('input_price', "Tổng tiền đầu vào");

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });

        return $form;
    }
}
