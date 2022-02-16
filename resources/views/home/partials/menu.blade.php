<!-- Navigation-->
<nav class="navbar navbar-expand-lg navbar-light fixed-top" id="mainNav">
    <div class="container px-4 px-lg-5">
        <a class="navbar-brand" href="{{ route('home.index') }}" style="padding: 1.5rem 0 !important;">{{ config('admin.name') }}</a>
        <button class="navbar-toggler navbar-toggler-right btn" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
            Menu
            <i class="fas fa-bars"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="{{route('home.service')}}">Dịch vụ</a></li>
                <li class="nav-item"><a class="nav-link" href="">Bảng giá</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('home.about') }}">Giới thiệu</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('home.proxy') }}">Chính sách khiếu nại</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('home.register') }}">Đăng ký</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('admin.login') }}">Đăng nhập</a></li>
            </ul>
        </div>
    </div>
</nav>