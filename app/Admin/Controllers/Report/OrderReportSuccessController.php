<?php

namespace App\Admin\Controllers\Report;

use App\Models\OrderReport\OrderReport;
use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class OrderReportSuccessController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Báo cáo đặt hàng trong ngày - theo ngày thành công';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new OrderReport());
        $grid->model()->orderBy('order_at', 'desc')->whereType(2);

        if (isset($_GET['month'])) {
            $grid->header(function () {
                $month = $_GET['month'];

                $records = OrderReport::where('order_at', 'like', $month.'-%')->whereType(2)->get();
                $number = $amount = $final_payment = $percent_service = $offer_cn = $offer_vn = $total = $index = 0;
                foreach ($records as $record) {
                    $data = json_decode($record->content);
                    if ($data != "") {
                        foreach ($data as $user) {
                            $number += $user->number;
                            $amount += $user->amount;
                            $final_payment += $user->final_payment;
                            $percent_service += $user->percent_service;
                            $offer_cn += $user->offer_cn;
                            $offer_vn += $user->offer_vn;
                            $total += $user->total;
                        }
                    }
                }

                $html = "";
                $html .= "Số lượng đơn: " . $number . "<br>";
                $html .= "Tổng tiền đơn hàng: " . $amount . "<br>";
                $html .= "Tổng tiền thanh toán: " . $final_payment . "<br>";
                $html .= "Tổng phí dịch vụ: " . $percent_service . "<br>";
                $html .= "Tổng đàm phán tệ: " . $offer_cn . "<br>";
                $html .= "Tổng đàm phán VND: " . number_format($offer_vn) . "<br>";
                $html .= "Tổng thực đặt: " . $total . "<br>";

                return $html;
            });
        }

        $grid->filter(function ($filter) {
            $filter->expand();
            $filter->disableIdFilter();

            $filter->date('order_at', 'Ngày đặt hàng')->date();
            $filter->where(function ($query) {
                
            }, 'Tháng VD: 2021-11', 'month');
            
        });

        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT');
        $grid->order_at('Ngày dặt hàng')->display(function () {
            return date('Y-m-d', strtotime($this->order_at));
        });
        $grid->content('Chi tiết')->display(function () {

            if ($this->content != "") {
                $data = json_decode($this->content);
                return view('admin.system.report.order_report', compact('data'));
            }
        });

        $grid->column('updated_at', "Ngày cập nhật")->display(function () {
            return date('H:i | d-m-Y', strtotime($this->updated_at));
        });
        
        $grid->disableExport();
        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->paginate(31);
        $grid->disableActions();
        $grid->disableCreateButton();

        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Alert);

        $form->text('title', "Tiêu đề")->rules(['required']);
        $form->summernote('content', "Nội dung")->rules(['required']);
        $isDefault = [
            'off' => ['value' => 0, 'text' =>  'Tắt', 'color' => 'danger'],
            'on'  => ['value' => 1, 'text' => 'Mở', 'color' => 'success']
        ];
        $form->switch('status', 'Loại kho')->states($isDefault)->default(1);
        $form->hidden('created_user_id')->default(Admin::user()->id);
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });

        return $form;
    }
}
