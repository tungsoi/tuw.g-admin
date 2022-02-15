<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>{{ config('admin.name') }}</title>

        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">

        <link rel="icon" type="image/x-icon" href="{{ asset('home/images/favicon.png') }}" />
        <link href="{{ asset('home/css/styles.css') }}?time={{now()}}" rel="stylesheet" />
    </head>
    <body id="page-top">
        @include('home.partials.menu')
        <div>
            @yield('content') 
        </div>

        {{-- @include('home.partials.modal') --}}
        
       
        <!-- About-->
       
        <!-- Projects-->
       
        <!-- Signup-->
      
        <!-- Contact-->
        <section class="contact-section bg-black">
           
        </section>
        <!-- Footer-->
        @include('home.partials.footer')
        {{-- <footer class="footer bg-black small text-center text-white-50"><div class="container px-4 px-lg-5">Copyright &copy; Alilogi.vn 2021</div></footer> --}}
        <!-- Bootstrap core JS-->
    </body>

    <!-- jQuery library -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <!-- Latest compiled JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>


    <script>
        $("button.navbar-toggler").on('click', function () {
            $('#navbarResponsive').toggle();
        });
    </script>

    

    @if (isset($alert) && $alert)

        <script>
            $(window).on('load', function() {
                $('#alert-mdl').modal('show');
            });
        </script>

    @endif
</html>
