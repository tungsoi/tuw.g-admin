<?php

namespace App\Admin\Actions\Export;

use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\SaleReport\SaleSalary;
use App\Models\SaleReport\SaleSalaryDetail;
use App\User;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid\Exporters\AbstractExporter;
use Maatwebsite\Excel\Classes\LaravelExcelWorksheet;
use Maatwebsite\Excel\Facades\Excel;
use PHPExcel_Style_Fill;
use Maatwebsite\Excel\Events\AfterSheet;

class SaleSalaryDetailExporter extends AbstractExporter
{
    protected $sale_salary_id;

    public function __construct($sale_salary_id)
    {
        $this->sale_salary_id = $sale_salary_id;
    }
    public function export()
    {
        $sale_salary_id = $this->sale_salary_id;
        Excel::create('DS_Khách_Hàng_Chi_Tiết_Sale_'.date('Ymd', strtotime(now())), function($excel) use ($sale_salary_id) {

            $excel->sheet('Sheet1', function(LaravelExcelWorksheet $sheet) use ($sale_salary_id) {

                $this->chunk(function ($records) use ($sheet, $sale_salary_id) {
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

                    $sale_salary_id = $this->sale_salary_id;

                    $report = SaleSalary::find($sale_salary_id);
            
                    $amount = $users->where('wallet', '<', 0)->sum('wallet');
            
                    $all_customers = $report->all_customer;
                    $not_action_customers = SaleSalaryDetail::where('sale_salary_id', $sale_salary_id)
                        ->where('po_payment', 0)
                        ->where('po_not_success_payment', 0)
                        ->where('trs_payment', 0)
                        ->get();
                    $action_customers = SaleSalaryDetail::where('sale_salary_id', $sale_salary_id)
                    ->whereNotIn('customer_id', $not_action_customers->pluck('customer_id'))
                    ->count();
                    
                    $rows = [];
                    $rows[] = [
                        "",
                        "Nhân viên",
                        $report->employee->name
                    ];
                    $rows[] = [
                        "",
                        "Thời gian cập nhật",
                        $report->updated_at
                    ];
                    $rows[] = [
                        "",
                        "Tổng âm ví khách hàng",
                        number_format($amount) . " VND"
                    ];
                    $rows[] = [
                        "",
                        "Khách hàng phát sinh doanh thu / Tổng số khách hàng",
                        $action_customers." /".$all_customers ." = " . (number_format($action_customers / $all_customers * 100, 1) ) . "%"
                    ];
                    $rows[] = [
                        "",
                        "Tổng số khách hàng cũ",
                        $report->old_customer
                    ];
                    $rows[] = [
                        "",
                        "Tổng số khách hàng mới",
                        $report->new_customer
                    ];
                    $rows[] = [
                        "",
                        "Tổng số khách hàng",
                        $all_customers
                    ];
                    $rows[] = [
                        "",
                        "Thời gian xuất báo cáo",
                        date('Y-m-d H:i:s', strtotime(now()))
                    ];
                    $rows[] = [
                        "",
                        "Người xuất",
                        Admin::user()->name
                    ];
                    $rows[] = [];
                    $rows[] = $this->header();
                    foreach ($users as $key => $user) {

                        $rows[] = [
                            $key+1,
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

                    $sheet->rows($rows);
                    $sheet->getStyle('A11:R11')->applyFromArray(array(
                        'font' => [
                            'bold' => true,
                            'size'      =>  13,
                        ],
                        'fill' => array(
                            'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'DFBE00')
                        )
                    ));

                    $sheet->getStyle('B1:C9')->applyFromArray(array(
                        'font' => [
                            'bold' => true,
                            'size'      =>  13,
                        ],
                        'fill' => array(
                            'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'FCD5B4')
                        )
                    ));
                }, 1000);

            });

        })->export('xlsx');
    }

    public function header()
    {
        return [
            'STT',
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