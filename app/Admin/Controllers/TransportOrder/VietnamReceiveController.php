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
        $form->column(1/2, function ($form) {
            $form->text('customer_code_input', 'Mã khách hàng')->rules(['required']);
        });
        $form->column(1/2, function ($form) {
            $form->select('ware_house_id', 'Kho nhận hàng')
                ->options(Warehouse::pluck('name', 'id'))
                ->rules(['required']);
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
