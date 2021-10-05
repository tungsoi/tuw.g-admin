<?php

namespace App\Admin\Actions\TransportCode;

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
        $file = $request->file('file');

        $data = Excel::load($file, function($reader) {
            $reader->setHeaderRow(0);
        }, 'UTF-8')->get();

        foreach ($data->toArray() as $key => $row) {
            $date = $request->create_at;
            

            if (is_array($row) && sizeof($row) >= 1) {

                $item = array_values($row);
                $temp = [
                    'transport_code' => (string) $item[0],
                    'advance_drag'   => $item[1] ?? 0,
                    'china_receive_at'  =>  $date. " 00:00:01",
                    'kg'    =>  0,
                    'length' =>  0,
                    'width' =>  0,
                    'height' =>  0,
                    'china_receive_user_id' =>  Admin::user()->id,
                    'internal_note' =>  'import',
                    'status'    =>  0
                ];

                $flag = TransportCode::whereTransportCode($temp['transport_code'])->get();

                if ($flag->count() == 0) {
                    TransportCode::firstOrCreate($temp);
                }
            }
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