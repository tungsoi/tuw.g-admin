<?php

namespace App\Admin\Controllers\ReportAr;

use App\Models\ArReport\Unit;
use App\Models\ReportWarehouse\ReportWarehousePortal;
use App\Models\TransportOrder\TransportCode;
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
            $filter->where(function ($query) {
                $title = TransportCode::whereNotNull('title')->where('customer_code_input', $this->input)->pluck('title')->toArray();

                return $query->whereIn('title', array_unique(array_values($title)));
                
            }, 'Mã khách hàng', 'customer_code_input');
        });

        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT');
        $grid->title('Mã lô')->display(function () {
            $html = $this->title;
            $html .= "<hr>";
            $html .= "<span>Tổng: ".$this->transportCode->count()."</span><br>";
            $html .= "<span>Đã về kho: ".$this->transportCode->where('status', 1)->count()."</span><br>";
            $html .= "<span>Đã xuất kho: ".$this->transportCode->where('status', 3)->count()."</span><br>";
            $html .= "<span>Luân chuyển: ".$this->transportCode->where('status', 4)->count()."</span>";

            return $html;
        });

        $grid->column('output', 'Đầu ra')->display(function () {
            $amount_output = $this->amount_output();

            return view('admin.system.package_report',compact('amount_output'));
        });
        $grid->column('input_count', 'Số lượng đầu vào')->editable();
        $grid->column('input_price', 'Đơn giá')->display(function () {
            return number_format($this->input_price);
        })->editable();
        $grid->column('input_type', 'Đơn vị')->editable('select', [
            1 => 'kg',
            2   =>  'm3'
        ]);
        $grid->column('amount_input', 'Tổng tiền đầu vào')->display(function () {
            return number_format( $this->amount_input() );
        });
        $grid->column('revenue', 'Lãi/Lỗ')->display(function () {
            $amount_output = $this->amount_output()['amount'];
            $amount_input =  $this->amount_input();

            $final = $amount_output - $amount_input;
            if ($final < 0) {
                return "<b style='color: red;'>".number_format($final)."</b>";
            } else {
                return "<b style='color: green;'>".number_format($final)."</b>";
            }
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

        $form->saving(function (Form $form) {
            $form->input_price = (int) str_replace(",", "", $form->input_price);
        });

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });

        return $form;
    }
}
