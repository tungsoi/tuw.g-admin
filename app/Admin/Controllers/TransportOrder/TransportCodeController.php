<?php

namespace App\Admin\Controllers\TransportOrder;

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

        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT');
        $grid->order_id('Mã đơn hàng');
        $grid->transport_code('Mã vận đơn');
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
        $grid->status('Trạng thái')->display(function () {
            return $this->getStatus();
        });
        $grid->disableExport();
        $grid->disableFilter();
        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->paginate(20);
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();

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

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });   

        return $form;
    }


}
