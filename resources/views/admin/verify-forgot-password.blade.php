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
                <a href="{{ admin_url('/') }}" style="text-transform: uppercase; color: white !important;"><b>{{config('admin.name')}}</b></a>
            </h1>
        </div>
        <!-- /.login-logo -->
        <div class="login-box-body">
            <p class="login-box-msg">Xác nhận đổi mật khẩu</p>

            <form action="{{ route('home.postVerifyForgotPassword') }}" method="post">
                <div class="form-group has-feedback {!! !$errors->has('password') ?: 'has-error' !!}">

                    @if($errors->has('password'))
                        @foreach($errors->get('password') as $message)
                            <label class="control-label" for="inputError"><i
                                class="fa fa-times-circle-o"></i> {{$message}}</label><br>
                        @endforeach
                    @endif

                    <input type="password" class="form-control" placeholder="{{ trans('admin.password') }}"
                        name="password">
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>
                <div class="form-group has-feedback {!! !$errors->has('password_confirm') ?: 'has-error' !!}">

                    @if($errors->has('password_confirm'))
                        @foreach($errors->get('password_confirm') as $message)
                            <label class="control-label" for="inputError"><i
                                class="fa fa-times-circle-o"></i> {{$message}}</label><br>
                        @endforeach
                    @endif

                    <input type="password" class="form-control" placeholder="Mật khẩu xác nhận"
                        name="password_confirmation">
                    <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>
                <div class="row">
                    <div class="col-xs-12">
                        <input type="hidden" name="_token" value="{{ $token }}">
                        <input type="hidden" name="email" value="{{ $email }}">
                        <button type="submit"
                            class="btn btn-success btn-block btn-flat">Đổi mật khẩu</button>
                    </div>
                    <!-- /.col -->
                </div>
                <div class="row">
                    <hr>
                    <div class="col-xs-6">
                    </div>

                    <div class="col-xs-6">
                        <a href="{{ route('admin.login') }}" style="float: right">{{ trans('admin.login') }}</a>
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

    </script>
</body>

</html>
