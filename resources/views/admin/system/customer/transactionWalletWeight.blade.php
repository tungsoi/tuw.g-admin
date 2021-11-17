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
            <h4>Số dư ví cân</h4>
            <b>{{ $customer->wallet_weight ?? 0 }}</b>
        </div>
    </div>
    <div class="col-md-3">
        <div class="alert alert-success">
            <h4>Số giao dịch dùng ví cân</h4>
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
        <th>Số cân</th>
        <th>Nội dung giao dịch</th>
    </thead>
    <tbody>
        @if ($data->count() > 0)
            @foreach ($data as $key => $transaction)
                <tr>
                    <td>{{ $key+1 }}</td>
                    <td align="center">{{ date('H:i | d-m-Y', strtotime($transaction->created_at)) }}</td>
                    <td align="center">{{ $transaction->userCreated->name }}</td>
                    <td align="center">
                        @if (strpos($transaction->content, "Thanh toán") !== false )
                            <span style="color: red">-{{ $transaction->kg }}</span>
                        @else 
                            <span style="color: green">+{{ $transaction->kg }}</span>
                        @endif
                       </td>
                    <td align="center">{{ $transaction->content }}</td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>