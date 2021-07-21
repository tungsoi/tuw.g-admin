<?php

namespace App\Admin\Controllers\TransportOrder;

use App\Models\System\Warehouse;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Illuminate\Http\Request;
use App\Models\TransportOrder\TransportCode;

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
}
