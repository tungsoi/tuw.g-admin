<?php

namespace App\Admin\Controllers\TransportOrder;

use App\Models\System\Warehouse;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Illuminate\Http\Request;
use App\Models\TransportOrder\TransportCode;

class ChinaReceiveController extends AdminController
{
    protected $title = "Nhập hàng Trung quốc nhận";

    protected function grid()
    {
        $form = new Form(new TransportCode);
        $form->setTitle(' ');
        $form->setAction('china_receives/save');

        $mode = isset($_GET['mode']) ? $_GET['mode'] : "";
        $callbackObj = null;

        if ($mode != null && $mode == 'popup')
        {
            $callback = isset($_GET['callback']) ? $_GET['callback'] : [];

            if (sizeof($callback)) {
                $callbackObj = TransportCode::whereIn('id', $callback)->get();
            }
        }

        $form->html(
            view("admin.system.transport_order.china_receive", compact('mode', 'callbackObj'))->render()
        );

        $form->hidden('created_user_id')->default(Admin::user()->id);
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
            $tools->disableList();
        });

        return $form;
    }

    public function storeTransportCode(Request $request)
    {
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
