<?php

namespace App\Admin\Controllers\TransportOrder;

use App\Models\System\Warehouse;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Illuminate\Http\Request;
use App\Models\TransportOrder\TransportCode;

class VietnamReceiveController extends AdminController
{
    protected $title = "Nhập hàng Việt nam nhận";

    protected function grid()
    {
        // admin_warning(
        //     'Hướng dẫn', 
        //     'Bước 1: Nhập mã khách hàng 
        //     <br> Bước 2: Bắn từng mã vận đơn
        //     <br> Bước 3: Click "Lưu dữ liệu"'
        // );
        $form = new Form(new TransportCode);
        $form->setTitle(' ');
        $form->setAction('vietnam_receives/storeVietnamReceive');

        $mode = isset($_GET['mode']) ? $_GET['mode'] : "";
        $callbackObj = null;

        if ($mode != null && $mode == 'popup')
        {
            $callback = isset($_GET['callback']) ? $_GET['callback'] : [];

            if (sizeof($callback)) {
                $callbackObj = TransportCode::whereIn('id', $callback)->get();
            }
        }
        $form->column(1/2, function ($form) {
            $form->text('customer_code_input', 'Mã khách hàng')->rules(['required']);
        });
        $form->column(1/2, function ($form) {
            $warehouse = Warehouse::all();

            $data = [];
            foreach ($warehouse as $w) {
                $data[$w->id] = $w->name . " (".$w->code." - ".$w->address.") ";
            }
            $form->select('ware_house_id', 'Kho nhận hàng')
                ->options($data)
                ->rules(['required'])
                ->default(1);
        });
        $form->column(12, function ($form) use ($mode, $callbackObj) {
            $form->divider();
            $form->html(
                view("admin.system.transport_order.vietnam_receive", compact('mode', 'callbackObj'))->render()
            );
        });

        $form->hidden('created_user_id')->default(Admin::user()->id);
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
            $tools->disableList();
        });

        $form->confirm('Xác nhận lưu dữ liệu nhập hàng ?');

        return $form;
    }

    public function storeTransportCode(Request $request)
    {
        dd($request->all());
        TransportCode::create([
            'transport_code'    =>  $request->transport_code,
            'status'            =>  TransportCode::CHINA_RECEIVE,
            'china_recevie_at'  =>  now(),
            'china_receive_user_id' =>  Admin::user()->id,
            'ware_house_id' =>  Warehouse::whereIsDefault(1)->first()->id
        ]);

        return response()->json([
            'code'  =>  200,
            'data'  =>  $request->all()
        ]);
    }
}
