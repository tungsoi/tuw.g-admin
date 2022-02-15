@extends('home.layout')
@section('content')
    @include('home.partials.banner')
    <section class="projects-section bg-light" id="projects">
        <div class="container px-4 px-lg-5">
            <!-- Featured Project Row-->
            <div class="row align-items-center">
                <div class="col-lg-12">
                    <div class="text-center text-lg-left">
                        <h4>QUY TRÌNH ĐẶT HÀNG</h4>
                        <br>
                    </div>
                </div>
                <div class="col-lg-12">
                    <img class="img-fluid mb-3 mb-lg-0" src="{{ asset('images/timeline.png') }}" alt="..." />
                </div>
                
            </div>
            <!-- Project One Row-->
            
    </section>
    <section class="signup-section" id="signup">
        <div class="container px-4 px-lg-5">
            <div class="row gx-4 gx-lg-5" style="margin-top: -50px">
                <div class="col-md-4 mb-3 mb-md-0">
                    {{-- card py-4 h-100 --}}
                    <div class="py-4 h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-map-marked-alt text-primary mb-2"></i>
                            <img src="https://dathangquangchau.info/newdhqc/image-demo/doitien.png" alt="">
                            <h4 class="text-uppercase m-0">PHÍ NHẬP HỘ THẤP</h4>
                            <div class="small text-black-50"> Chỉ 10.000đ</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="py-4 h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-envelope text-primary mb-2"></i>
                            <img src="https://dathangquangchau.info/newdhqc/image-demo/order.png" alt="">
                            <h4 class="text-uppercase m-0">ĐẶT CỌC TIỀN HÀNG ÍT</h4>
                            <div class="small text-black-50">Chỉ 70% tiền hàng</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="py-4 h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-mobile-alt text-primary mb-2"></i>
                            <img src="https://dathangquangchau.info/newdhqc/image-demo/order.png" alt="">
                            <h4 class="text-uppercase m-0">CAM KẾT ĐẶT ĐÚNG HÀNG</h4>
                            <div class="small text-black-50">Đúng link và theo yêu cầu</div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- <div class="social d-flex justify-content-center">
                <a class="mx-2" href="#!"><i class="fab fa-twitter"></i></a>
                <a class="mx-2" href="#!"><i class="fab fa-facebook-f"></i></a>
                <a class="mx-2" href="#!"><i class="fab fa-github"></i></a>
            </div> --}}
        </div>
    </section>
@stop
