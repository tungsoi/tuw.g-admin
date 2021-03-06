<?php

namespace App\Admin\Actions\Export;

use App\Models\PurchaseOrder\PurchaseOrder;
use App\User;
use Encore\Admin\Grid\Exporters\AbstractExporter;
use Maatwebsite\Excel\Classes\LaravelExcelWorksheet;
use Maatwebsite\Excel\Facades\Excel;
use PHPExcel_Style_Fill;
use Maatwebsite\Excel\Events\AfterSheet;

class PurchaseOrdersExporter extends AbstractExporter
{
    public function export()
    {
        Excel::create('DS_Đơn_hàng_mua_hộ_'.date('Ymd', strtotime(now())), function($excel) {

            $excel->sheet('Sheet1', function(LaravelExcelWorksheet $sheet) {

                $this->chunk(function ($records) use ($sheet) {
                    $ids = $records->map(function ($order) {
                        return $order->id;
                    });

                    $orders = PurchaseOrder::whereIn('id', $ids)
                        ->with('customer')
                        ->with('statusText')
                        ->with('warehouse')
                        ->with('orderEmployee')
                        ->with('createdUser')
                        ->with('depositedUser')
                        ->with('orderedUser')
                        ->with('vnReceiveUser')
                        ->with('successedUser')
                        ->with('items')
                        ->with('userCancle')
                        ->get();
                    
                    $rows = [];
                    foreach ($orders as $key => $order) {
                        $price_rmb = $order->sumItemPrice();
                        $price_vnd = str_replace(",", "", $order->sumItemPrice()) * $order->current_rate;
                        $deposite = $price_vnd / 100 * 70;

                        $rows[] = [
                            $key+1,
                            $order->order_number,
                            $order->current_rate,
                            $order->items->where('status', '!=', 4)->count()." link, ". $order->totalItems() . " sp",
                            $order->order_type,
                            $order->statusText->name ?? "",
                            $order->shop_name,
                            $order->customer->symbol_name ?? "",
                            $order->customer->saleEmployee ? $order->customer->saleEmployee->name : null,
                            $order->orderEmployee ? $order->orderEmployee->name : null,
                            $order->warehouse ? $order->warehouse->name : null,
                            $price_rmb,
                            number_format($price_vnd),
                            number_format($deposite),
                            $order->purchase_order_service_fee != "" ? $order->purchase_order_service_fee : 0,
                            $order->sumShipFee(),
                            $order->amount(),
                            number_format(str_replace(",", "", $order->amount()) * $order->current_rate),
                            $order->sumItemWeight(),
                            number_format($order->deposited),
                            (int) number_format(
                                number_format(str_replace(",", "", $order->amount()) * $order->current_rate, 0, '.', '')
                                - number_format($order->deposited, 0, '.', '')
                            , 0, '.', ''), 
                            $order->transport_code,
                            $order->customer_note,
                            $order->admin_note,
                            $order->internal_note,
                            $order->created_at != null ? date('H:i | d-m-Y', strtotime($order->created_at)) : "",
                            $order->createdUser->name ?? "",
                            $order->deposited_at != null ? date('H:i | d-m-Y', strtotime($order->deposited_at)) : "",
                            $order->depositedUser->symbol_name ?? "",
                            $order->order_at != null ? date('H:i | d-m-Y', strtotime($order->order_at)) : "",
                            $order->orderedUser->symbol_name ?? "",
                            $order->vn_receive_at != null ? date('H:i | d-m-Y', strtotime($order->vn_receive_at)) : "",
                            $order->vnReceiveUser->name ?? "",
                            $order->success_at != null ? date('H:i | d-m-Y', strtotime($order->success_at)) : "",
                            $order->successedUser->name ?? "",
                            $order->cancle_at != null ? date('H:i | d-m-Y', strtotime($order->cancle_at)) : "",
                            $order->userCancle->name ?? ""
                        ];
                    }

                    array_unshift($rows, $this->header());
                    $sheet->rows($rows);
                    $sheet->getStyle('A1:AJ1')->applyFromArray(array(
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
            'Mã đơn hàng',
            'Tỷ giá',
            'Link / Sản phẩm',
            'Loại đơn hàng',
            'Trạng thái',
            'Tên Shop',
            'Mã khách hàng',
            'NVKD',
            'NVDH',
            'Kho hàng',
            'Tổng giá sản phẩm (Tệ)',
            'Tổng giá sản phẩm (VND)',
            'Tiền cọc mặc định (70%) (VND)',
            'Phí dịch vụ (Tệ)',
            'Phí vận chuyển nội địa (Tệ)',
            'Tổng giá cuối (Tệ)',
            'Tổng giá cuối (VND)',
            'Tổng cân (KG)',
            'Đã cọc (VND)',
            'Công nợ (VND)',
            'Mã vận đơn',
            'Khách hàng ghi chú',
            'Admin ghi chú',
            'Ghi chú nội bộ',
            'Ngày tạo',
            'Người tạo',
            'Ngày cọc',
            'Người cọc',
            'Ngày đặt hàng',
            'Người xác nhận đặt hàng',
            'Ngày về kho Việt Nam',
            'Người xác nhận về Kho',
            'Ngày thành công',
            'Người xác nhận thành công',
            'Ngày huỷ',
            'Người huỷ'
        ];
    }
}