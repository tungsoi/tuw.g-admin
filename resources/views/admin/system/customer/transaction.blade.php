@if ($empty)
    <style>
        .column-__actions__ {
            display: none;
        }
    </style>
@endif
<div class="row">
    <div class="col-md-3">
        <div class="alert alert-warning">
            <h4>Mã khách hàng</h4>
            <b>{{ $customer->symbol_name }}</b>
        </div>
    </div>
    <div class="col-md-3">
        <div class="alert alert-info">
            <h4>Số dư ví hiện tại</h4>
            <b>{{ number_format($customer->wallet) }} VND</b>
        </div>
    </div>
    <div class="col-md-3">
        <div class="alert alert-success">
            <h4>Số giao dịch</h4>
            <b>
                @if (is_array($data) && sizeof($data) > 0)
                    {{ sizeof($data) }}
                @else
                    0
                @endif
            </b>
        </div>
    </div>
    <div class="col-md-3">
        <div class="alert alert-danger">
            <h4>Giao dịch gần nhất</h4>
            <b>
                @if (is_array($data) && sizeof($data) > 0)
                    {{ $data[0]['payment_date'] }}
                @else
                    0
                @endif
            </b>
        </div>
    </div>
</div>
<br>

@if (isset($mode) && $mode == 'recharge' && $form != "")
    {!! $form !!}
@endif

<table class="table table-bordered">
    <thead>
        <th>STT</th>
        <th>Ngày giao dịch</th>
        <th>Người tạo</th>
        <th>Cập nhật</th>
        <th>Người sửa</th>
        <th>Đơn hàng</th>
        <th>Loại giao dịch</th>
        <th>Nội dung giao dịch</th>
        <th>Số dư đầu kỳ (VND)</th>
        <th>Trừ tiền (VND)</th>
        <th>Nạp tiền (VND)</th>
        <th>Số dư cuối kỳ (VND)</th>
        <th>Thao tác</th>
    </thead>
    <tbody>
        @if (is_array($data) && sizeof($data) > 0)
        @foreach ($data as $transaction)
            <tr>
                <td align="center">{{ $transaction['order'] }}</td>
                <td align="center">{{ $transaction['payment_date'] }}</td>
                <td>{{ $transaction['user_id_created'] }}</td>
                <td>{{ date('H:i | d-m-Y', strtotime($transaction['updated_at'])) }}</td>
                <td>{{ $transaction['updated_user_id'] }}</td>
                <td>{{ $transaction['order_link'] }}</td>
                <td align="center">{{ $transaction['type_recharge'] }}</td>
                <td>{{ $transaction['content'] }}</td>
                <td align="right">{!! $transaction['before_payment'] !!}</td>
                <td align="right">{!! $transaction['down'] !!}</td>
                <td align="right">{!! $transaction['up'] !!}</td>
                <td align="right">{!! $transaction['after_payment'] !!}</td>
                <td>
                    @php
                        $route = route('admin.customers.transactions', $customer->id) . "?mode=recharge&transaction_id=" . $transaction['id'];
                    @endphp
                    <a href="{{ $route }}" class="grid-row-edit btn btn-xs btn-warning" data-toggle="tooltip" title="" data-original-title="Chỉnh sửa">
                        <i class="fa fa-edit"></i>
                    </a>

                    <a href="javascript:void(0);" data-id="1" class="grid-row-delete btn btn-xs btn-danger" data-toggle="tooltip" title="Xóa">
                        <i class="fa fa-trash"></i>
                    </a>
                </td>
            </tr>
        @endforeach
        @endif
    </tbody>
</table>