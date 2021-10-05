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
        $file = $request->file('file');
        ini_set('precision', 50);
        $data = Excel::load($file, function($reader) {
            $reader->setHeaderRow(0);
        }, 'UTF-8')->get();

        $job = new ImportTransportCode(
            $data->toArray(),
            $request->create_at,
            Admin::user()->id
        );
        dispatch($job);

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