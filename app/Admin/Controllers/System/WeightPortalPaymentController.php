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

class WeightPortalPaymentController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Lịch sử khách hàng dùng cân thanh toán';
    protected $current_kg = 'Tổng cân hiện tại';
    protected $add_kg = 'Thêm cân';
    protected $add_employee = 'Chia cân đến nhân viên';
    protected $add_customer = 'Chia cân đến khách hàng';
    protected $used_customer = 'Khách hàng dùng cân thanh toán';


    public function offerOrderScript() {
        return <<<SCRIPT
            $('.btn-success').parent().prev().remove();

            $('tfoot').each(function () {
                $(this).insertAfter($(this).siblings('thead'));
            });
SCRIPT;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new TransactionWeight());
        $grid->model()->whereType(1)->orderBy('id', 'desc');
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->expand();
            $customer = User::whereIsCustomer(User::CUSTOMER)
            ->whereIsActive(User::ACTIVE)
            ->orderBy('id', 'desc')
            ->get()
            ->pluck('symbol_name', 'id');

            $filter->equal('customer_id', 'Khách hàng')->select($customer);
        });
        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT');
        $grid->customer_id('Khách hàng')->display(function () {
            return $this->customer->symbol_name;
        });
        $grid->kg('Cân nặng (KG)')->totalRow();
        $grid->content('Nội dung');
        
        $grid->column('created_at', "Ngày tạo")->display(function () {
            return date('H:i | d-m-Y', strtotime($this->created_at));
        })->style('text-align: center');

        $grid->disableCreateButton();
        // $grid->disableFilter();
        $grid->disableExport();
        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->disableActions();

        Admin::script($this->offerOrderScript());

        return $grid;
    }
}
