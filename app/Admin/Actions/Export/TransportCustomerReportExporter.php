<?php

namespace App\Admin\Actions\Export;

use App\Models\TransportCustomerReport;
use App\User;
use Encore\Admin\Grid\Exporters\AbstractExporter;
use Maatwebsite\Excel\Classes\LaravelExcelWorksheet;
use Maatwebsite\Excel\Facades\Excel;
use PHPExcel_Style_Fill;
use Maatwebsite\Excel\Events\AfterSheet;

class TransportCustomerReportExporter extends AbstractExporter
{
    protected $id;
    
    public function __construct($id)
    {
        $this->id = $id;
    }

    public function export()
    {

        $report = TransportCustomerReport::find($this->id);

        $data = User::selectRaw(
            "admin_users.*, admin_users.symbol_name, count(*) as count, sum(payment_orders.amount) as amount, sum(payment_orders.total_kg) as kg,
            sum(payment_orders.total_m3) as m3, sum(payment_orders.total_advance_drag) as advance_drag")
        ->join('payment_orders', 'payment_orders.payment_customer_id', 'admin_users.id')
        ->where("payment_orders.created_at", ">=", $report->begin)
        ->where("payment_orders.created_at", "<=", $report->finish)
        ->where('payment_orders.status', 'payment_export')
        ->groupBy("admin_users.id")
        ->orderBy("amount", "desc")
        ->get();

        Excel::create('Sản lượng vận chuyển_'.date('Ymd', strtotime(now())), function($excel) use ($data, $report) {

            $excel->sheet('Sheet1', function(LaravelExcelWorksheet $sheet) use ($data, $report) {

                foreach ($data as $key => $value) {
                    $rows[] = [
                        $key+1,
                        $value->symbol_name,
                        number_format($value->wallet),
                        $value->count,
                        number_format( $value->amount),
                        number_format( $value->kg),
                        number_format( $value->amount),
                        number_format( $value->advance_drag),
                    ];
                } 
                array_unshift($rows, $this->header());

                $rows[] = [
                    '',
                    $report->title,
                    $report->begin,
                    $report->finish,
                ];
                $rows[] = [
                    ''
                ];
                $rows[] = [
                    ''
                ];
                $sheet->rows($rows);
                $sheet->getStyle('A1:H1')->applyFromArray(array(
                    'font' => [
                        'bold' => true,
                        'size'      =>  13,
                    ],
                    'fill' => array(
                        'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'DFBE00')
                    )
                ));

            });

        })->export('xlsx');
    }

    public function header()
    {
        return [
            'STT', 
            'Mã khách hàng', 
            'Ví tiền',
            "SỐ LƯỢNG ĐƠN",
            "TỔNG DOANH THU (VND)",
            "TỔNG CÂN (KG)",
            "TỔNG KHỐI (M3)",
            "TỔNG ỨNG KÉO (VND)",
        ];
    }

    public function typeCustomer($type) {
        $data = [
            0 => 'Chưa chọn',
            1 => 'Khách hàng Vận chuyển',
            2 => 'Khách hàng Order',
            3 => 'Order + Vận chuyển'
        ];

        return $data[$type] ?? "";
    }
}