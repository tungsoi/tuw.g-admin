<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{config('admin.title')}} | Quên mật khẩu</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <link rel="shortcut icon" href="{{ asset('assets/img/favicon.png') }}">

    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="{{ admin_asset("vendor/laravel-admin/AdminLTE/bootstrap/css/bootstrap.min.css") }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ admin_asset("vendor/laravel-admin/font-awesome/css/font-awesome.min.css") }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ admin_asset("vendor/laravel-admin/AdminLTE/dist/css/AdminLTE.min.css") }}">
    <!-- iCheck -->
    <link rel="stylesheet" href="{{ admin_asset("vendor/laravel-admin/AdminLTE/plugins/iCheck/square/blue.css") }}">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
  <script src="//oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>

<body class="hold-transition login-page"
    @if(config('admin.login_background_image'))style="background: url({{ asset(config('admin.login_background_image')) }}) no-repeat;background-size: cover;"
    @endif>
    <div class="login-box">
        <div class="login-logo">
            <h1>
                <a href="{{ admin_url('/') }}" style="text-transform: uppercase; color: #464646 !important;"><b>{{config('admin.name')}}</b></a>
            </h1>
        </div>
        <!-- /.login-logo -->
        <div class="login-box-body" style="border: 1px solid grey; border-radius: 10px;">
            <p class="login-box-msg">Quên mật khẩu</p>

            <form action="{{ route('home.postForgotPassword') }}" method="post" id="frm-forgot-password">
                <div class="form-group has-feedback @if (isset($errors)) {!! !$errors->has('username') ?: 'has-error' !!} @endif">

                    @if($errors->has('username'))
                        @foreach($errors->get('username') as $message)
                            <label class="control-label" for="inputError"><i
                                class="fa fa-times-circle-o"></i> {{$message}}</label><br>
                        @endforeach
                    @endif

                    <input type="text" class="form-control" placeholder="Địa chỉ Email" name="username"
                        value="{{ old('username') }}">
                    <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <button type="submit"
                            class="btn btn-success btn-block btn-flat">Gửi email xác thực</button>
                    </div>
                    <!-- /.col -->
                </div>
                <div class="row">
                    <hr>

                    <div class="col-xs-12" style="text-align: center;">
                        <p>
                            Đã có tài khoản ?
                        <a href="{{ route('admin.login') }}">{{ trans('admin.login') }}</a></p>

                        <hr>
                        <p><a href="https://alilogi.vn">Trang chủ</a></p>
                    </div>
                </div>
            </form>

        </div>
        <!-- /.login-box-body -->
    </div>
    <!-- /.login-box -->

    <!-- jQuery 2.1.4 -->
    <script src="{{ admin_asset("vendor/laravel-admin/AdminLTE/plugins/jQuery/jQuery-2.1.4.min.js")}} "></script>
    <!-- Bootstrap 3.3.5 -->
    <script src="{{ admin_asset("vendor/laravel-admin/AdminLTE/bootstrap/js/bootstrap.min.js")}}"></script>
    <!-- iCheck -->
    <script src="{{ admin_asset("vendor/laravel-admin/AdminLTE/plugins/iCheck/icheck.min.js")}}"></script>
    <script>
        $(function () {
            $('input').iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue',
                increaseArea: '20%' // optional
            });
        });

        $('form#frm-forgot-password').submit(function(){
            $(this).find(':input[type=submit]').prop('disabled', true);
        });
    </script>
</body>

</html>
