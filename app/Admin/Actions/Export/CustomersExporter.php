<?php

namespace App\Admin\Actions\Export;

use App\User;
use Encore\Admin\Grid\Exporters\AbstractExporter;
use Maatwebsite\Excel\Classes\LaravelExcelWorksheet;
use Maatwebsite\Excel\Facades\Excel;
use PHPExcel_Style_Fill;
use Maatwebsite\Excel\Events\AfterSheet;

class CustomersExporter extends AbstractExporter
{
    public function export()
    {
        Excel::create('DSKH_'.date('Ymd', strtotime(now())), function($excel) {

            $excel->sheet('Sheet1', function(LaravelExcelWorksheet $sheet) {

                $this->chunk(function ($records) use ($sheet) {
                    $ids = $records->map(function ($order) {
                        return $order->id;
                    });

                    $orders = User::whereIn('id', $ids)
                        ->whereIsCustomer(User::CUSTOMER)
                        ->with('warehouse')
                        ->with('saleEmployee')
                        ->with('orderEmployee')
                        ->with('percentService')
                        ->with('transactions')
                        ->with('purchaseOrders')
                        ->with('paymentOrders')
                        ->get();

                    $rows = [];
                    foreach ($orders as $key => $item) {
                        $rows[] = [
                            $key+1,
                            $item->name,
                            $item->symbol_name,
                            $item->username,
                            $item->phone_number,
                            number_format($item->wallet),
                            $item->address,
                            $item->warehouse->name ?? "",
                            $item->saleEmployee->name ?? "",
                            $item->orderEmployee->name ?? "",
                            $item->percentService->name ?? "",
                            $this->typeCustomer($item->type_customer),
                            $item->wallet_weight,
                            $item->default_price_kg,
                            $item->default_price_m3,
                            $item->transactions->last() ? date('d-m-Y', strtotime($item->transactions->last()->created_at)) : "",
                            $item->purchaseOrders->last() ? date('d-m-Y', strtotime($item->purchaseOrders->last()->created_at)) : "",
                            $item->paymentOrders->last() ? date('d-m-Y', strtotime($item->paymentOrders->last()->created_at)) : ""
                        ];
                    }

                    array_unshift($rows, $this->header());
                    $sheet->rows($rows);
                    $sheet->getStyle('A1:R1')->applyFromArray(array(
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

            });

        })->export('xlsx');
    }

    public function header()
    {
        return [
            'STT', 
            'T??n kh??ch h??ng',
            'M?? kh??ch h??ng', 
            'Email', 
            'S??? ??i???n tho???i',
            'V?? ti???n',
            "?????a ch???",
            "Kho h??ng",
            "Nh??n vi??n kinh doanh",
            "Nh??n vi??n ?????t h??ng",
            "Ph?? d???ch v???",
            "Lo???i kh??ch h??ng",
            "V?? c??n",
            "Gi?? c??n",
            "Gi?? kh???i",
            "Giao d???ch g???n nh???t",
            "????n Order g???n nh???t",
            "????n v???n chuy???n g???n nh???t"
        ];
    }

    public function typeCustomer($type) {
        $data = [
            0 => 'Ch??a ch???n',
            1 => 'Kh??ch h??ng V???n chuy???n',
            2 => 'Kh??ch h??ng Order',
            3 => 'Order + V???n chuy???n'
        ];

        return $data[$type] ?? "";
    }
}