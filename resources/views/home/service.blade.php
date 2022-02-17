@extends('home.layout')
@section('content')
<section class="projects-section">
    @foreach ($data as $item)
        <div class="container px-4 px-lg-5 item-service">
            <div class="img-service">
                <img src="{{ URL::asset('/uploads/'.$item['img']) }}" alt="">
            </div>
            <div class="content-service">
                <p class="title-service">{{$item['title']}}</p>
                <p class="time-service">{{$item['created_at']}}</p>
                <p class="description-service">{{$item['description']}}</p>
            </div>
        </div>
    @endforeach
</section>
<style>
    .item-service{
        display: flex;
        width: 100%;
        list-style: none;
        padding: 10px;
        box-sizing: border-box;
        border: 1px solid #ddd;
        margin-bottom: 20px;
    }
    .content-service{
        margin:0px 20px;
    }
    .title-service{
        font-weight: bold;
        font-size: 18px;
        color: #333;
    }
    .time-service{
        color: #888;
    }
    .img-service img{
        width: 200px;
        height: 150px;
    }
    .description-service{

    }
</style>
@stop