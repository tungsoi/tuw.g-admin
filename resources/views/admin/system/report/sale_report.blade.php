<div class="col-lg-6">
    <style>
        img {
            width: 60px;
            height: 60px;
            border-radius: 20px;
            border: 0.5px solid black;
        }
        .col-md-4 {
            text-align: center;
        }
        .top-two {
            margin-top: 50px;
        }
        .top-three {
            margin-top: 70px;
        }
    </style>
    <div class="row" id="top-three">
        @foreach ($top as $key => $user)
            @php
                $order = 2;
                switch ($key) {
                    case 0:
                        $order = "top-two";
                        break;
                    case 1:
                        $order = "top-one";
                        break;
                    case 2:
                        $order = "top-three";
                        break;
                }
    
                $avatar = $user['avatar'] != "" ? $user['avatar'] : config('admin.default_avatar');
            @endphp
            <div class="col-md-4 {{ $order }}">
                <img src="{{ $avatar }}" alt="">
                <h5>{{ $user['name'] }}</h5>
            </div>  
        @endforeach
    </div>
    <hr>
    <div class="row" style="text-align: center;">
        @foreach ($other as $key => $user)
            @php
                $order = 2;
                switch ($key) {
                    case 0:
                        $order = "top-two";
                        break;
                    case 1:
                        $order = "top-one";
                        break;
                    case 2:
                        $order = "top-three";
                        break;
                }
    
                $avatar = $user['avatar'] != "" ? $user['avatar'] : config('admin.default_avatar');
            @endphp
    
            <div class="col-md-12">
                <img src="{{ $avatar }}" alt="">
                <h5>Top {{ ($key+3) }}. {{ $user['name'] }}</h5>
            </div>  
        @endforeach
    </div>
</div>