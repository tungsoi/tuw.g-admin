
<div class="col-md-12">
    <div class="alert alert-danger">
        <h4>Phòng kế toán</h4>
        <hr>
        <p><b>Giao dịch: <span class="pull-right">{{ $revenue['count'] }}</span></b></p>
        <p><b>Tiền mặt: <span class="pull-right">{{ number_format($revenue['cash_money']) }} VND</span></b></p>
        <p><b>Chuyển khoản: <span class="pull-right">{{ number_format($revenue['cash_banking']) }} VND</span></b></p>
        <hr>
        <h4><b>Tổng: <span class="pull-right">{{ number_format($revenue['cash_banking'] + $revenue['cash_money']) }} VND</span></b></h4>
        <hr>
        <a href="{{ $revenue['route'] }}" style="text-decoration: none !important;">
            <i class="fa fa-eye" aria-hidden="true"></i> &nbsp; 
            <b> Chi tiết</b>
        </a>
    </div>
</div>