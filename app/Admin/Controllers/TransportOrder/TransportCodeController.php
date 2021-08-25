<?php

namespace App\Admin\Controllers\TransportOrder;

use App\Admin\Services\UserService;
use App\Models\System\Alert;
use App\Models\System\Warehouse;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Illuminate\Http\Request;
use App\Models\TransportOrder\TransportCode;
use Encore\Admin\Grid;

class TransportCodeController extends AdminController
{
    protected $title = "Mã vận đơn";

    public function search($transportCode) {
        $data = TransportCode::whereTransportCode($transportCode)->first();
        return response()->json([
            'code'  =>  200,
            'data'  =>  $data
        ]);
    }

    public function grid() 
    {
        $grid = new Grid(new TransportCode());
        $grid->model()->orderBy('id', 'desc');

        $userService = new UserService();

        $grid->header(function () {
            return "<b style='color: red'>Chưa check query khi ở màn hình customer -> chỉ lấy các mã vận đơn thuộc các đơn thanh toán của khách hàng này</b>";
        });
        $grid->expandFilter();
        $grid->filter(function($filter) use ($userService) {
            $filter->expand();
            $filter->disableIdFilter();
            $filter->column(1/4, function ($filter) use ($userService) {
                
                $filter->like('transport_code', 'Mã vận đơn');

                if (! Admin::user()->isRole('customer')) {
                    $filter->equal('customer_id', 'Mã khách hàng')->select($userService->GetListCustomer());
                }
                
            });
            $filter->column(1/4, function ($filter)  {
                $filter->like('customer_code_input', 'Khách hàng vận đơn');
            });

            $filter->column(1/4, function ($filter) {
                $filter->between('china_recevie_at', 'Ngày về TQ')->date();
            });

            $filter->column(1/4, function ($filter) {
                $filter->between('vietnam_recevie_at', 'Ngày về VN')->date();
            });

            Admin::style('
                #filter-box label {
                    padding: 0px !important;
                    padding-top: 10px;
                    font-weight: 600;
                    font-size: 12px;
                }
                #filter-box .col-sm-2 {
                    width: 100% !important;
                    text-align: left;
                    padding: 0px 15px 3px 15px !important;
                }
                #filter-box .col-sm-8 {
                    width: 100% !important;
                }
            ');
        });


        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT');
        $grid->order_id('Mã đơn hàng')->style('color: red');
        $grid->transport_code('Mã vận đơn');
        $grid->customer_code_input('Khách hàng vận đơn');
        $grid->customer_payment('Khách hàng thanh toán')->style('color: red');
        $grid->kg('KG');
        $grid->length('Dài (cm)');
        $grid->width('Rộng (cm)');
        $grid->height('Cao (cm)');
        $grid->v('V/6000')->display(function () {
            return $this->v();
        });
        $grid->m3('M3')->display(function () {
            return $this->m3();
        });
        $grid->advance_drag('Ứng kéo (Tệ)');
        $grid->price_service('Giá vận chuyển')->display(function () {
            return number_format($this->price_service); 
        });
        $grid->amount('Tổng tiền')->style('color: red');
        $grid->payment_type('Loại thanh toán')->display(function () {
            return $this->paymentType();
        });
        $grid->status('Trạng thái')->display(function () {
            return $this->getStatus();
        })->style('color: red');
        $grid->warehouse()->name('Kho hàng');
        $grid->china_recevie_at('Ngày về TQ')->display(function () {
            return $this->china_recevie_at != null ? date('H:i | d-m-Y', strtotime($this->china_recevie_at)) : null;
        });

        $grid->vietnam_recevie_at('Ngày về VN')->display(function () {
            return $this->vietnam_recevie_at != null ? date('H:i | d-m-Y', strtotime($this->vietnam_recevie_at)) : null;
        });

        if (! Admin::user()->isRole('customer')) {
            $grid->admin_note('Ghi chú')->editable();
        } else {
            $grid->disableActions();
        }
        

        $grid->disableCreateButton();
        $grid->disableExport();

        if (Admin::user()->isRole('customer')) {
            $grid->disableBatchActions();
        }
        $grid->disableColumnSelector();
        $grid->paginate(20);
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
            $actions->disableDelete();

            if ($this->row->order_id != "") {
                // $actions->disableEdit();
                // $actions->disableDelete();
            }
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
        $form = new Form(new TransportCode);

        $form->text('customer_code_input', 'Mã khách hàng')->rules(['required']);
        $form->text('transport_code', "Mã vận đơn")->rules(['required']);

        $form->currency('kg', "KG")->rules(['required'])->digits(1)->symbol('KG');
        $form->number('length', "Dài (cm)")->rules(['required']);
        $form->number('width', "Rộng (cm)")->rules(['required']);
        $form->number('height', "Cao (cm)")->rules(['required']);
        $form->currency('advance_drag', "Ứng kéo")->rules(['required'])->symbol('Tệ');
        $form->text('admin_note', 'Admin ghi chú');

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });   

        return $form;
    }


}
