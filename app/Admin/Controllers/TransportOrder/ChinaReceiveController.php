<?php

namespace App\Admin\Controllers\TransportOrder;

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

        // $form->confirm('Xác nhận lưu dữ liệu nhập hàng ?');

        return $form;
    }

    protected function save(Request $request) {
        $transportCodeArr = $request->transport_code;
        $createdUserId = $request->created_user_id;
        $ids = [];

        if (is_array($transportCodeArr) && sizeof($transportCodeArr) > 0) {
            foreach ($transportCodeArr as $transportCode) {
                if ($transportCode != null) {
                    $data = [
                        'transport_code'        =>  $transportCode,
                        'status'                =>  TransportCode::CHINA_RECEIVE,
                        'china_receive_user_id' =>  $createdUserId,
                        'china_recevie_at'      =>  now()
                    ];

                    $flag = TransportCode::where('transport_code', $data['transport_code'])->count();
                    
                    if ($flag == 0)
                    {
                        $res = TransportCode::create($data);
                        $ids[] = $res->id;
                    }
                }
            }
        }

        return redirect()->route('admin.china_receives', ['mode' => 'popup', 'callback'   =>  $ids]);
    }
}
