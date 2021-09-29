
<div class="col-md-6">
    <div class="alert alert-warning">
        <h4>Số đơn hàng</h4>
        <hr>
        <h4><b>{{ $orders->count() }}</b></h4>

    </div>
</div>

<div class="col-md-6">
    <div class="alert alert-danger">
        <h4>Tổng tiền</h4>
        <hr>
        <h4><b>{{ number_format($orders->sum('amount')) }} VND</b></h4>

    </div>
</div>