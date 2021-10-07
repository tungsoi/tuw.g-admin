@php
    $color = ['info', 'success', 'warning', 'info'];
@endphp
@foreach ($warehouses as $key => $warehouse)

<div class="col-md-3">
    <div class="alert alert-{{ $color[$key] }}">
        <h4>{{ $warehouse->name }}</h4>
        <hr>
        <p><b>Giao dịch: <span class="pull-right">{{ $revenue[$warehouse->id]['count'] }}</span></b></p>
        <p><b>Tiền mặt: <span class="pull-right">{{ number_format($revenue[$warehouse->id]['cash_money']) }} VND</span></b></p>
        <p><b>Chuyển khoản: <span class="pull-right">{{ number_format($revenue[$warehouse->id]['cash_banking']) }} VND</span></b></p>
        <hr>
        <h4><b>Tổng: <span class="pull-right">{{ number_format($revenue[$warehouse->id]['cash_banking'] + $revenue[$warehouse->id]['cash_money']) }} VND</span></b></h4>
        <hr>
        <a href="{{ $revenue[$warehouse->id]['route'] }}" style="text-decoration: none !important;">
            <i class="fa fa-eye" aria-hidden="true"></i> &nbsp; 
            <b> Chi tiết</b>
        </a>
    </div>
</div>

@endforeach