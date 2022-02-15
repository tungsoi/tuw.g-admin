<?php

namespace App\Admin\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\System\WeightPortal;
use App\Models\System\TransactionWeight;
use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Encore\Admin\Layout\Row;
use Encore\Admin\Layout\Column;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\InfoBox;
use Illuminate\Http\Request;

class WeightPortalCompanyController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Lịch sử công ty thêm cân';
    protected $current_kg = 'Tổng cân hiện tại';
    protected $add_kg = 'Thêm cân';
    protected $add_employee = 'Chia cân đến nhân viên';
    protected $add_customer = 'Chia cân đến khách hàng';
    protected $used_customer = 'Khách hàng dùng cân thanh toán';
    
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new WeightPortal());
        $grid->model()->whereType(2)->orderBy('id', 'desc');

        // $grid->header(function () {
        //     return '<a href="'.route('admin.weight_portals.create').'" class="btn btn-sm btn-success" title="Thêm cân">
        //     <i class="fa fa-plus"></i><span class="hidden-xs"> Thêm cân</span>
        // </a>';
        // });
        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT');
        $grid->userCreate()->name('Người tạo');
        $grid->value('Cân nặng (KG)')->totalRow();
        $grid->price('Giá cân ước tính (VND)')->display(function () {
            return number_format($this->price);
        });
        $grid->amount('Tổng giá trị (VND)')->display(function () {
            return number_format($this->value * $this->price);
        });
        $grid->content('Nội dung');
        
        $grid->column('created_at', "Ngày tạo")->display(function () {
            return date('H:i | d-m-Y', strtotime($this->created_at));
        })->style('text-align: center');

        $grid->disableFilter();
        $grid->disableExport();
        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->disableActions();

        Admin::script($this->offerOrderScript());

        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new WeightPortal());

        $form->setTitle($this->add_kg);

        $form->text('user_created_name', 'Người thực hiện')->default(Admin::user()->name)->disable();
        $form->text('time', 'Ngày thực hiện')->default(date('H:i | d-m-Y', strtotime(now())))->disable();
        $form->currency('default', 'Số cân tổng còn lại')->default(WeightPortal::whereType(1)->first()->value)->disable()->digits(1)->symbol('KG');
        $form->divider();

        $form->hidden('user_created_id')->default(Admin::user()->id);

        $form->hidden('type')->default(2);
        $form->hidden('user_receive_id');

        $form->currency('value', 'Số cân')->digits(1)->symbol('KG')->rules(['required']);
        $form->currency('price', 'Ước tính giá trên 1 KG')->digits(0)->symbol('VND');
        $form->text('content', 'Nội dung')->rules(['required']);
       
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });
        
        $form->saved(function (Form $form) {
            $type = $form->model()->type;

            if ($type == 2) {
                $value = $form->model()->value;
                $res = WeightPortal::whereType(1)->first();
                $res->value += $value;
                $res->save();
            } else {
                $value = $form->model()->value;
                $res = WeightPortal::whereType(1)->first();
                $res->value -= $value;
                $res->save();

                $user = User::find($form->model()->user_receive_id);
                $user->wallet_weight += $value;
                $user->save();
            }
            

            admin_toastr('Chỉnh sửa thành công', 'success');
            return redirect()->route('admin.weight_portals_company.index');
        });

        Admin::script($this->offerOrderScript());

        return $form;
    }

    public function offerOrderScript() {
        return <<<SCRIPT
            $('.btn-success').parent().prev().remove();

            $('tfoot').each(function () {
                $(this).insertAfter($(this).siblings('thead'));
            });
SCRIPT;
    }
}
