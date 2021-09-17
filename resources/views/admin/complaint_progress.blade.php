
<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">

<div class="w3-container">
    <div class="w3-light-grey">
        <div class="w3-green" style="height:24px;width:{{$percent == 0 ? "100" : $percent}}%">
            <p> &nbsp;Tỉ lệ khiếu nại thành công / Tổng khiếu nại : ({{ $complaint_success ." / ". $complaint }}) = {{$percent}}%</p>
        </div>
    </div><br>
</div>