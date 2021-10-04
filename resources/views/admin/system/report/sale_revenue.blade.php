<div>
    <div class="col-md-3">
        <div class="alert alert-info">
            <h4>Số nhân viên kinh doanh</h4>
            <hr>
            <h4>{{ $sales->count() }}</h4>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="alert alert-success">
            <h4>Đơn chưa hoàn thành</h4>
            <hr>
            <h4>{{ $detail->sum('processing_order') }} đơn <span class="pull-right">{{ number_format($process) }} VND</span></h4>
        </div>
    </div>

    <div class="col-md-3">
        <div class="alert alert-warning">
            <h4>Đơn hoàn thành</h4>
            <hr>
            <h4>{{ $detail->sum('success_order') }} đơn <span class="pull-right">{{ number_format($success) }} VND</span></h4>
        </div>
    </div>


    <div class="col-md-3">
        <div class="alert alert-danger">
            <h4>Tổng doanh số tháng</h4>
            <hr>
            <h4>* <span class="pull-right">{{ number_format($process + $success) }} VND</span></h4>
        </div>
    </div>
</div>
<div>
    <hr>
    <a href="{{ $route }}" style="text-decoration: none !important;">
        <i class="fa fa-eye" aria-hidden="true"></i> &nbsp; 
        <b> Chi tiết</b>
    </a>
</div>