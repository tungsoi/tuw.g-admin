<?php

namespace App\Admin\Actions\Export;

use App\Models\TransportOrder\TransportCode;
use App\User;
use Encore\Admin\Grid\Exporters\AbstractExporter;
use Maatwebsite\Excel\Classes\LaravelExcelWorksheet;
use Maatwebsite\Excel\Facades\Excel;
use PHPExcel_Style_Fill;
use Maatwebsite\Excel\Events\AfterSheet;

class TransportCodeExporter extends AbstractExporter
{
    public function export()
    {
        ini_set("memory_limit","256M");

        Excel::create('DSMVD_'.date('Ymd', strtotime(now())), function($excel) {

            $excel->sheet('Sheet1', function(LaravelExcelWorksheet $sheet) {

                $this->chunk(function ($records) use ($sheet) {
                    $ids = $records->map(function ($code) {
                        return $code->id;
                    });

                    $selected_ids = array(1,4,5,3,0);
                    $ids_ordered = implode(',', $selected_ids);

                    $codes = TransportCode::whereIn('id', $ids)
                        ->where('transport_code', '!=', "")
                        ->orderByRaw("FIELD(status, $ids_ordered)")
                        ->orderBy('vietnam_receive_at', 'desc')
                        ->orderBy('china_receive_at', 'desc')
                        ->orderBy('customer_code_input', 'desc')
                        ->with('paymentOrder')
                        ->with('warehouse')
                        ->with('statusText')
                        ->with('chinaRevUser')
                        ->with('vietnamRevUser')
                        ->get();

                    $rows = [];
                    foreach ($codes as $key => $item) {
                        $rows[] = [
                            $key+1,
                            strval($item->transport_code),
                            $item->paymentOrder->order_number ?? null,
                            // $item->getOrdernNumberPurchase(),
                            $item->customer_code_input,
                            $item->paymentOrder->paymentCustomer->symbol_name ?? "",
                            $item->kg,
                            $item->length,
                            $item->width,
                            $item->height,
                            $item->v(),
                            $item->m3,
                            $item->advance_drag,
                            $this->price_service($item),
                            $item->paymentType(),
                            $this->amount($item),
                            $item->china_receive_at != "" ? date('H:i | d-m-Y', strtotime($item->china_receive_at)) : "",
                            $item->chinaRevUser->name ?? "",
                            $item->vietnam_receive_at != "" ? date('H:i | d-m-Y', strtotime($item->vietnam_receive_at)) : "",
                            $item->vietnamRevUser->name ?? "",
                            $item->payment_at != "" ? date('H:i | d-m-Y', strtotime($item->payment_at)) : "",
                            $item->statusText->name,
                            $this->warehouse($item),
                            $item->admin_note
                        ];
                    }

                    array_unshift($rows, $this->header());
                    $sheet->rows($rows);
                    $sheet->getStyle('A1:X1')->applyFromArray(array(
                        'font' => [
                            'bold' => true,
                            'size'      =>  13,
                        ],
                        'fill' => array(
                            'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'DFBE00')
                        )
                    ));

                }, 1000);

            });

        })->export('xlsx');
    }

    public function warehouse($item) {
        if ($item->status == 4) {
            return ($item->warehouse->name ?? "") . " => " . ($item->warehouseSwap->name ?? "");
        }

        return $item->warehouse->name ?? "";
    }

    public function amount($item) {
        if ($item->payment_type == 1 && $item->paymentOrder) {
            return number_format($item->paymentOrder->price_kg * $item->kg);
        } else if ($item->payment_type == -1 && $item->paymentOrder) {
            return number_format($item->paymentOrder->price_m3 * $item->m3_cal());
        } else {
            return 0;
        } 
    }

    public function header()
    {
        return [
            'STT', 
            'Mã vận đơn', 
            'Mã đơn hàng vận chuyển',
            // 'Mã đơn hàng mua hộ',
            'Mã khách hàng', 
            'Khách hàng thanh toán',
            'Cân nặng',
            "Dài",
            "Rộng",
            "Cao",
            "V/6000",
            "M3",
            "Ứng kéo (Tệ)",
            "Giá vận chuyển",
            "Loại thanh toán",
            "Tổng tiền",
            "Ngày về kho Trung Quốc",
            "Người xác nhận về kho Trung Quốc",
            "Ngày về kho Việt Nam",
            "Người xác nhận về kho Việt Nam",
            "Ngày thanh toán",
            "Trạng thái hiện tại",
            "Kho hàng",
            "Ghi chú"
        ];
    }

    public function price_service($item) {
        if ($item->payment_type == 1 && $item->paymentOrder) {
            return number_format($item->paymentOrder->price_kg);
        } elseif ($item->payment_type == -1 && $item->paymentOrder) {
            return number_format($item->paymentOrder->price_m3);
        } else {
            return 0;
        }
    }
}