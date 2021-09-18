<style>
    img {
        width: 150px;
        height: 150px;
        border-radius: 20px;
        /* border: 0.5px solid black; */
    }
    .col-md-4 {
        text-align: center;
        width: 30%;
        border-bottom: 1px dashed black;
    }
    .top-two {
        margin-top: 100px;
    }
    .top-three {
        margin-top: 150px;
    }
    .col-md-4 p {
        font-size: 20px;
        font-weight: bold;
        color: black;
    }
</style>
<div class="col-lg-12">
    <div class="row" id="top-three">
        @foreach ($top as $key => $user)
            @php
                $order = 2;
                $image = "";
                $detail = "";
                switch ($key) {
                    case 0:
                        $order = "top-two";
                        $image = asset('images/ranked/top2.png');
                        $detail = "Top 2. ";
                        break;
                    case 1:
                        $order = "top-one";
                        $image = asset('images/ranked/top1.png');
                        $detail = "Top 1. ";
                        break;
                    case 2:
                        $order = "top-three";
                        $image = asset('images/ranked/top3.png');
                        $detail = "Top 3. ";
                        break;
                }
    
                $avatar = $user['avatar'] != "" ? $user['avatar'] : config('admin.default_avatar');
            @endphp
            <div class="col-md-4 {{ $order }}">
                <img src="{{ $image }}" alt="">
                <p>{{ $detail . $user['name'] }}</p>
            </div>  
        @endforeach
    </div>
    <hr>
    <div class="row" style="text-align: center;">
        @php
            $limit = (int) round(sizeof($normal)/6);

            $res = [];
            $begin = 3;
            $limit_r = 5;
            for ($index = 0; $index <= $limit_r; $index++) {
                $res[
                    'top'. ($begin+$index+1)
                ] = array_slice($normal, $index*$limit_r, $limit_r);
            }
        @endphp
        @php
            $i = 4;
        @endphp
        @foreach ($res as $key_row => $row)
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

            @foreach ($row as $key_value => $value)
                <div class="col-md-4">
                    <br>
                    <img src="{{ asset('images/ranked/'.$key_row.'.png') }}" alt="">
                    <h5>{{ $value['name'] }}</h5>
                    <br>
                </div> 
            @endforeach 

            @php
                $i++;
            @endphp
        @endforeach
    </div>
</div>