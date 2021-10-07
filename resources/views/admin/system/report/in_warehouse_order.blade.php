@php
    $color = ['info', 'success', 'warning', 'info'];
    $total_count = 0;
    $total_money = 0;
@endphp

@foreach ($warehouses as $key => $warehouse)

@php
    $total_count += $revenue[$warehouse->id]['count'];
    $total_money += $revenue[$warehouse->id]['money'];
@endphp
<div class="col-md-3">
    <div class="alert alert-{{ $color[$key] }}">
        <h4>{{ $warehouse->name }}</h4>
        <hr>
        <h4><b>Số đơn hàng: <span class="pull-right">{{ $revenue[$warehouse->id]['count'] }}</span></b></h4>
        <h4><b>Tổng tiền: <span class="pull-right">{{ number_format($revenue[$warehouse->id]['money']) }} VND</span></b></h4>
        <hr>
        <a href="{{ $revenue[$warehouse->id]['route'] }}" style="text-decoration: none !important;">
            <i class="fa fa-eye" aria-hidden="true"></i> &nbsp; 
            <b> Chi tiết</b>
        </a>
    </div>
</div>

@endforeach


<div class="col-md-3">
    <div class="alert alert-danger">
        <h4>Tất cả</h4>
        <hr>
        <h4><b>Số đơn hàng: <span class="pull-right">{{ $total_count }}</span></b></h4>
        <h4><b>Tổng tiền: <span class="pull-right">{{ number_format($total_money) }} VND</span></b></h4>
        <hr>
        <a href="{{ route('admin.payments.all') . "?status=payment_not_export" }}" style="text-decoration: none !important;">
            <i class="fa fa-eye" aria-hidden="true"></i> &nbsp; 
            <b> Chi tiết</b>
        </a>
    </div>
</div>
