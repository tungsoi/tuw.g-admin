@php
    $percent = (int) $percent;
@endphp
<div class="row">
    <div class="col-md-12">
        <h4>Phần trăm tiền đặt cọc trên các đơn hàng đã được chọn: <b style="color: red">{{ $percent }}% tổng tiền sản phẩm</b></h4>
    </div>
</div>
<table class="table table-bordered">
    <thead>
        <th>Mã đơn hàng</th>
        <th>Mã khách hàng</th>
        <th>Số dư ví (VND)</th>
        <th>Tổng tiền sản phẩm (VND)</th>
        <th>Tổng đơn (VND)</th>
        <th>Tiền cọc (VND)</th>
    </thead>
    <tbody>
        @foreach ($orders as $order)
            <tr style="text-align: right">
                <td>{{ $order->order_number }}</td>
                <td>
                    <p>{{ $order->customer->symbol_name }}</p>
                </td>
                <td>
                    {{ number_format($order->customer->wallet) }}
                </td>
                <td>{{ number_format(str_replace(",", "", $order->sumItemPrice()) * $order->current_rate) }}</td>
                <td>{{ number_format(str_replace(",", "", $order->amount()) * $order->current_rate) }}</td>
                <td>
                    <input type="hidden" name="id[]" value="{{ $order->id }}">
                    <input name="deposited[]" type="text" class="form-control deposited" value="{{ $order->depositeAmountCal('vn', $percent) }}">
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<script src="https://cdnjs.cloudflare.com/ajax/libs/autonumeric/4.1.0/autoNumeric.min.js"></script>
<script>
    $( document ).ready(function() {
        $(function() {
            new AutoNumeric.multiple('.deposited', {
                decimalPlaces: 0
            });
        });
    });
</script>