<div id="estimate_amount_booking">

    <style>
        #estimate_amount_booking .col-md-3 {
            display: none;
        }
    </style>

    <p style="text-align: center" id="loading">
        <i class="fa fa-spinner fa-spin" style="color: green"></i> Đang lấy dữ liệu ... 
    </p>

    <div class="col-md-3">
        <div class="alert alert-info">
            <h4>Số đơn hàng</h4>
            <hr>
            <h4><span class="pull-right" id="number_orders"></span></h4> <br>
        </div>
    </div>

    <div class="col-md-3">
        <div class="alert alert-success">
            <h4>Tổng tiền sản phẩm</h4>
            <hr>
            <h4><span class="pull-right" id="total_vnd"></span></h4> <br>
        </div>
    </div>

    <div class="col-md-3">
        <div class="alert alert-warning">
            <h4>Tổng tiền cọc</h4>
            <hr>
            <h4><span class="pull-right" id="total_deposited"></span></h4> <br>
        </div>
    </div>


    <div class="col-md-3">
        <div class="alert alert-danger">
            <h4>Tổng dự trù đặt hàng</h4>
            <hr>
            <h4><span class="pull-right" id="total_estimate"></span></h4> <br>
        </div>
    </div>
</div>

<div>
    <hr>
    <a href="{{ $route }}" style="text-decoration: none !important;">
        <i class="fa fa-eye" aria-hidden="true"></i> &nbsp; 
        <b> Chi tiết</b>
    </a>
</div>

<script>
    $( document ).ready(function() {
   
        $.ajax({
            url: "report_portals/calculatorEstimateAmountBooking",
            type: 'GET',
            dataType: "JSON",
            success: function (response)
            {
                if (response.status) {
                    $('#loading').hide();
                    $('#estimate_amount_booking .col-md-3').show();

                    $("#number_orders").html(response.data.number_orders);
                    // $("#total_rmb").html(response.data.total_rmb + " Tệ");
                    $("#total_vnd").html(response.data.total_vnd + " VND");
                    $("#total_deposited").html(response.data.total_deposited + " VND");
                    $("#total_estimate").html(response.data.total_estimate + " VND");
                }
            }
        });

    });
</script>