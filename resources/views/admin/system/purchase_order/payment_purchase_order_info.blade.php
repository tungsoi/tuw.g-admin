<table class="table table-bordered">
    <thead>
        <th>Mã đơn hàng</th>
        <th>Trạng thái</th>
        <th>Mã vận đơn</th>
        <th>Tổng giá sản phẩm</th>
        <th>Phí dịch vụ</th>
        <th>VC nội địa TQ</th>
        <th>Tổng giá cuối</th>
        <th>Đã cọc</th>
        <th>Còn thiếu</th>
    </thead>
    <tbody>
        <tr>
            <td style="text-align: center;">{{ $purchaseOrderData->order_number }}</td>
            <td style="text-align: center;">
                <span style='text-align: right' class='label label-{{$purchaseOrderData->statusText->label}}'>{{ $purchaseOrderData->statusText->name. $purchaseOrderData->countItemFollowStatus()}}</span>
            </td>
            <td>
                {{ $purchaseOrderData->transport_code }}
            </td>
            <td  style="text-align: right;">{{ number_format(str_replace(",", "", $purchaseOrderData->sumItemPrice()) * $purchaseOrderData->current_rate) }}</td>
            <td  style="text-align: right;">{{ number_format(str_replace(",", "", $purchaseOrderData->purchase_order_service_fee) * $purchaseOrderData->current_rate) }}</td>
            <td  style="text-align: right;">{{ number_format(str_replace(",", "", $purchaseOrderData->sumShipFee()) * $purchaseOrderData->current_rate) }}</td>
            <td  style="text-align: right;">{{ number_format(str_replace(",", "", $purchaseOrderData->amount()) * $purchaseOrderData->current_rate) }}</td>
            <td  style="text-align: right;">{{ number_format($purchaseOrderData->deposited) }}</td>
            <td style="color: red; text-align: right">{{ number_format( (str_replace(",", "", $purchaseOrderData->amount()) * $purchaseOrderData->current_rate) - $purchaseOrderData->deposited) }}</td>
        </tr>
    </tbody>
</table>