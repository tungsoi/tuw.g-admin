<?php

namespace App\Admin\Actions\TransportCode;

use App\Jobs\ImportTransportCode;
use App\Models\TransportOrder\TransportCode;
use Encore\Admin\Actions\Action;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportChinaReceive extends Action
{
    public $name = 'Import bảng mã vận đơn Trung quốc nhận';
    protected $selector = '.import-china-receive';

    public function handle(Request $request)
    {
        ini_set('precision', 20);
        $path = $request->file('file')->getRealPath();
        $data = Excel::load($path)->get();
        
        if ($data->count()) {
            foreach ($data as $key => $value) {
                $arr[] = [
                    'transport_code' => strval($value->ma_van_don),
                    'advance_drag'   => $value->ung_te,
                    'china_receive_at'  =>  $request->create_at. " 00:00:01",
                    'china_receive_user_id' =>  Admin::user()->id,
                    'internal_note' =>  'import',
                    'status'    =>  0
                ];
            }

            TransportCode::insert($arr);
        }

        return $this->response()->success('Import thành công')->refresh();
    }

    public function form()
    {
        $this->date('create_at', 'Ngày nhập vào')->default(now());
        $this->file('file', 'Chọn File');
    }

    public function html()
    {
        return '<a class="btn btn-sm btn-warning import-china-receive"><i class="fa fa-upload"></i> &nbsp;Import mã vận đơn Trung quốc nhận</a>';
    }
}