@extends('home.layout')
@section('content')
<section class="projects-section">
    {{-- <div class="container px-4 px-lg-5 item-service">
        <p>Bảng giá</p>
    </div> --}}
    <div class="cam-ket-dich-vu-block block-clear block-both">
        <div class="container">
            <div class="inner">
                <div class="content">
                    <div class="ck-top">
                        <h3>
                            Cam kết<br>
                            <span>Dịch vụ</span>
                        </h3>
                        <div class="text">
                            <p>Nhằm mang đến cho quý khách hàng dịch vụ nhập hàng tốt nhất, chúng tôi luôn nỗ lực cải tiền không ngừng nhằm nâng cao chất lượng phục vụ , đem đến sự hài lòng cho khách hàng sử dụng dịch vụ của chúng tôi !</p>
                            <div class="content animatedParent">
                                <ul>
                                    <li>
                                        <h4>Cam kết đặt hàng</h4>
                                        <p>Cam kết đền bù gấp 10 lần tiền hàng do Thương Đô đặt sai.</p>
                                    </li>
                                    <li class="animated bounceInRight go">
                                        <h4>Thời gian báo giá</h4>
                                        <p>Báo giá trong 8h kể từ lúc xuống đơn và đặt hàng trong 8h kể từ lúc khách hàng đặt cọc hoặc thanh toán.</p>
                                    </li>
                                    <li>
                                        <h4>Cam kết bồi thường</h4>
                                        <p>Cam kết bồi thường gấp 10 lần số tiền tiền chênh lệch nếu nhân viên tự tăng giá đơn hàng hoặc phí vận chuyển nội địa quốc tế.</p>
                                    </li>
                                    <li class="animated bounceInRight go">
                                        <h4>Tỉ giá</h4>
                                        <p>Tỷ giá ổn định chuẩn xác 100% theo ngân hàng Công Thương Việt Nam, rẻ nhất thị trường order hàng hiện nay.</p>
                                    </li>
                                    {{-- <li>
                                        <h4>Thời gian vận chuyển</h4>
                                        <p>Thời gian vận chuyển ổn định chuẩn xác.</p>
                                    </li> --}}
                                    {{-- <li class="animated bounceInRight go">
                                        <h4>Cam kết hỗ trợ</h4>
                                        <p>Chúng tôi phát triển công nghệ để hỗ trợ Quý khách đặt hàng và tra cứu đơn hàng 24/7.</p>
                                    </li> --}}
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
               
            </div>
        </div>
    </div>
</section>
<style>
    .cam-ket-dich-vu-block {
        background: url(https://www.thuongdo.com/sites/all/themes/giaodiennguoidung/images/world-bg.png) no-repeat center -101px #1a5694;
        min-height: 400px;
    }
    .cam-ket-dich-vu-block .container .inner {
        background: url(https://www.thuongdo.com/sites/all/themes/giaodiennguoidung/images/service-pop-bg.png) no-repeat 0px bottom transparent;
        display: inline-block;
        width: 100%;
        min-height: 490px;
    }
    
    .cam-ket-dich-vu-block .container .content {
        float: right;
        width: 60%;
    }
    .cam-ket-dich-vu-block .ck-top {
        width: 100%;
        float: left;
        color: white;
        margin-top: 40px;
    }
    .cam-ket-dich-vu-block .ck-top h3 {
        text-transform: uppercase;
        color: #f4c533;
        text-align: right;
        float: left;
        margin-right: 20px;
    }
    .cam-ket-dich-vu-block .ck-top .content {
        width: 80%;
        position: relative;
        overflow: hidden;
    }
   
    .cam-ket-dich-vu-block .ck-top {
        width: 100%;
        float: left;
        color: white;
        margin-top: 40px;
    }
   
    .cam-ket-dich-vu-block .ck-top .content ul li h4 {
        font-size: 14px;
        font-weight: normal;
        margin-bottom: 0px;
        color: #f4c533;
    }
    .cam-ket-dich-vu-block .ck-top .content ul {
        position: relative;
        z-index: 2;
        padding: 15px;
        padding-top: 0px;
    }   
    .cam-ket-dich-vu-block .ck-top .content {
        width: 80%;
        position: relative;
        overflow: hidden;
    }
    .cam-ket-dich-vu-block .ck-top .content ul li {
        float: left;
        width: 48%;
        margin-right: 2%;
        margin-bottom: 15px;
    }
    .block-clear ul li {
        list-style: none;
        margin: 0px;
    }
    .cam-ket-dich-vu-block .ck-top span {
        color: white;
        font-size: 30px;
    }
   
</style>
@stop   