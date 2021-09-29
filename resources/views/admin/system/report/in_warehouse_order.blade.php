@php
    $color = ['info', 'success', 'warning'];
@endphp
@foreach ($warehouses as $key => $warehouse)

<div class="col-md-4">
    <div class="alert alert-{{ $color[$key] }}">
        <h4>{{ $warehouse->name }}</h4>
        <hr>
        <h4><b>Số đơn hàng: <span class="pull-right">{{ $revenue[$warehouse->id]['count'] }}</span></b></h4>
        <h4><b>Tổng tiền: <span class="pull-right">{{ number_format($revenue[$warehouse->id]['money']) }} VND</span></b></h4>
    </div>
</div>

@endforeach