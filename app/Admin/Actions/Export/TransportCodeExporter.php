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

                });

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
            'M?? v???n ????n', 
            'M?? ????n h??ng v???n chuy???n',
            // 'M?? ????n h??ng mua h???',
            'M?? kh??ch h??ng', 
            'Kh??ch h??ng thanh to??n',
            'C??n n???ng',
            "D??i",
            "R???ng",
            "Cao",
            "V/6000",
            "M3",
            "???ng k??o (T???)",
            "Gi?? v???n chuy???n",
            "Lo???i thanh to??n",
            "T???ng ti???n",
            "Ng??y v??? kho Trung Qu???c",
            "Ng?????i x??c nh???n v??? kho Trung Qu???c",
            "Ng??y v??? kho Vi???t Nam",
            "Ng?????i x??c nh???n v??? kho Vi???t Nam",
            "Ng??y thanh to??n",
            "Tr???ng th??i hi???n t???i",
            "Kho h??ng",
            "Ghi ch??"
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