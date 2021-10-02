<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="renderer" content="webkit">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ Admin::title() }} @if($header) | {{ $header }}@endif</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <link rel="shortcut icon" href="{{ asset('home/images/favicon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400&display=swap" rel="stylesheet">


    {!! Admin::css() !!}

    <script src="{{ Admin::jQuery() }}"></script>
    {!! Admin::headerJs() !!}
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

     <style>
        .main-header {
            height: 0px !important;
            display: none;
        }
        @media only screen and (max-width: 900px) {
            .main-header {
                display: block;
                height: auto !important;
            }

            .box-header, .box-body {
                overflow: scroll;
            }
        }

        tfoot {
            display: table-row-group !important;
            background: #3c763d !important;
            color: white !important;
            font-weight: bold !important;
        }
     </style>
</head>

<body class="hold-transition {{config('admin.skin')}} {{join(' ', config('admin.layout'))}}">

@if($alert = config('admin.top_alert'))
    <div style="text-align: center;padding: 5px;font-size: 12px;background-color: #ffffd5;color: #ff0000;">
        {!! $alert !!}
    </div>
@endif

<div class="loading-overlay">
    <i class="fa fa-spinner fa-spin" style="color: green"></i> Vui lòng đợi trong giây lát ...
</div>

<div class="wrapper">

    @include('admin::partials.header')

    @include('admin::partials.sidebar')

    <div class="content-wrapper" id="pjax-container">
        {!! Admin::style() !!}
        <div id="app">
        @yield('content')
        </div>
        {!! Admin::script() !!}
        {!! Admin::html() !!}
    </div>

    {{-- @include('admin::partials.footer') --}}
</div>

{{-- <button id="totop" title="Go to top" style="display: none;"><i class="fa fa-chevron-up"></i></button> --}}

<script>
    function LA() {}
    LA.token = "{{ csrf_token() }}";
    LA.user = @json($_user_);
</script>

<!-- REQUIRED JS SCRIPTS -->
{!! Admin::js() !!}

<script>
    function copyElementText(id) {
        var text = document.getElementById(id).innerText;
        text = text.replace("MH-", "");
        var elem = document.createElement("textarea");
        document.body.appendChild(elem);
        elem.value = text;
        elem.select();
        document.execCommand("copy");
        document.body.removeChild(elem);

        $.admin.toastr.success('Đã Copy', '', {timeOut: 1000});
    }

    $(document).on('click', '#btn-print-this-page', function () {
        window.print();
    })
</script>
</body>
</html>
