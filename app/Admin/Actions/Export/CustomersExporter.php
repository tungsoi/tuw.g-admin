<?php

namespace App\Admin\Actions\Export;

use App\User;
use Encore\Admin\Grid\Exporters\AbstractExporter;
use Maatwebsite\Excel\Classes\LaravelExcelWorksheet;
use Maatwebsite\Excel\Facades\Excel;

class CustomersExporter extends AbstractExporter
{
    public function export()
    {
        Excel::create('DSKH_'.date('Ymd', strtotime(now())), function($excel) {

            $excel->sheet('Sheet1', function(LaravelExcelWorksheet $sheet) {

                $this->chunk(function ($records) use ($sheet) {

                    $flag = 1;
                    $rows = $records->map(function ($item) use ($flag) {

                        $res = [
                            $flag,
                            $item->symbol_name,
                            $item->username,
                            $item->phone_number,
                            $item->address,
                            number_format($item->wallet),
                            $item->saleStaff->name ?? ""
                        ];

                        $flag++;

                        return $res;
                    });
                    $rows->prepend($this->header());

                    $sheet->rows($rows);

                });

            });

        })->export('xlsx');
    }

    public function header()
    {
        return [
            'STT', 
            'Mã khách hàng', 
            'Email', 
            'Số điện thoại', 
            'Địa chỉ',
            'Số dư ví',
            'Nhân viên Sale'
        ];
    }
}