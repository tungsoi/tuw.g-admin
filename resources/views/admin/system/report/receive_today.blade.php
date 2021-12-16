<div>
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

    <div class="col-md-3">
        <div class="alert alert-warning">
            <h4>Tổng M3</h4>
            <hr>
            <h4><b>{{ $codes->sum('m3') }} M3</b></h4>
        </div>
    </div>
</div>

<div class="col-md-12">
    <a href="{{ $route }}" style="text-decoration: none !important;">
        <i class="fa fa-eye" aria-hidden="true"></i> &nbsp; 
        <b> Chi tiết</b>
    </a>
</div>