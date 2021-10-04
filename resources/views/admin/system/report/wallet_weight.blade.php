<div class="col-md-3">
    <div class="alert alert-info">
        <h4>Số mã vận đơn về hôm nay</h4>
        <hr>
        <h4><b>{{ $codes->count() }}</b></h4>
    </div>
</div>

<div class="col-md-3">
    <div class="alert alert-success">
        <h4>Tổng cân</h4>
        <hr>
        <h4><b>{{ $codes->sum('kg') }} KG</b></h4>
    </div>
</div>

{{-- <div class="col-md-3">
    <div class="alert alert-warning">
        <h4>Tổng khối</h4>
        <hr>
        <h4><b></b></h4>
    </div>
</div> --}}