<?php

namespace App\Admin\Actions\Export;

use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\SaleReport\SaleSalaryDetail;
use App\User;
use Encore\Admin\Grid\Exporters\AbstractExporter;
use Maatwebsite\Excel\Classes\LaravelExcelWorksheet;
use Maatwebsite\Excel\Facades\Excel;
use PHPExcel_Style_Fill;
use Maatwebsite\Excel\Events\AfterSheet;

class SaleSalaryDetailExporter extends AbstractExporter
{
    public function export()
    {
        Excel::create('DS_Khách_Hàng_Chi_Tiết_Sale_'.date('Ymd', strtotime(now())), function($excel) {

            $excel->sheet('Sheet1', function(LaravelExcelWorksheet $sheet) {

                $this->chunk(function ($records) use ($sheet) {
                    $ids = $records->map(function ($order) {
                        return $order->id;
                    });

                    $users = SaleSalaryDetail::whereIn('id', $ids)
                        ->orderByRaw('CONVERT(po_success, SIGNED) desc')
                        ->orderByRaw('CONVERT(po_not_success, SIGNED) desc')
                        ->orderByRaw('CONVERT(trs, SIGNED) desc')
                        ->orderByRaw('CONVERT(wallet, SIGNED) asc')
                        ->with('customer')
                        ->with('report')
                        ->get();

                    
                    $rows = [];
                    foreach ($users as $key => $user) {

                        $rows[] = [
                            $key+1,
                            $user->report->employee->name,
                            $user->customer->symbol_name,
                            date('H:i | d-m-Y', strtotime($user->customer->created_at)),
                            number_format($user->wallet),
                            number_format($user->po_success),
                            number_format($user->po_payment),
                            number_format($user->po_service_fee),
                            number_format($user->po_rmb, 2),
                            number_format($user->po_offer),
                            number_format($user->po_not_success),
                            number_format($user->po_not_success_payment),
                            number_format($user->po_not_success_service_fee),
                            number_format($user->po_not_success_deposite),
                            number_format($user->po_not_success_owed),
                            number_format($user->trs, 1),
                            number_format($user->trs_kg, 1),
                            number_format($user->trs_m3, 3),
                            number_format($user->trs_payment)
                        ];
                    }

                    array_unshift($rows, $this->header());
                    $sheet->rows($rows);
                    $sheet->getStyle('A1:S1')->applyFromArray(array(
                        'font' => [
                            'bold' => true,
                            'size'      =>  13,
                        ],
                        'fill' => array(
                            'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'DFBE00')
                        )
                    ));
                }, 500);

            });

        })->export('xlsx');
    }

    public function header()
    {
        return [
            'STT',
            'NHÂN VIÊN KINH DOANH',
            'MÃ KHÁCH HÀNG',
            'NGÀY TẠO TÀI KHOẢN',
            'SỐ DƯ',
            'ĐƠN ORDER THÀNH CÔNG',
            'DOANH SỐ',
            'PHÍ DỊCH VỤ',
            'TỔNG GIÁ TỆ',
            'TỔNG ĐÀM PHÁN',
            'ĐƠN HÀNG ORDER CHƯA HOÀN THÀNH',
            'DOANH SỐ',
            'PHÍ DỊCH VỤ',
            'TỔNG CỌC',
            'CÔNG NỢ TRÊN ĐƠN',
            'ĐƠN HÀNG VẬN CHUYỂN',
            'TỔNG KG',
            'TỔNG M3',
            'DOANH THU'
        ];
    }
}