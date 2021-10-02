<div class="col-md-12">
    <h4>Tổng tiền cọc dự tính: <span id='estimate-deposited' style='color: red; font-weight: 700;'></span> (VND) </h4>
    <h4>Tổng tiền đơn dự tính: <span id='estimate-amount-rmb' style='color: green; font-weight: 700;'></span> (Tệ) </h4>
</div>

@if (! Admin::user()->isRole('customer'))
<div class="col-md-12">
    <hr>
    <button class="btn btn-sm btn-success" id="btn-get-list-customer">
        Lấy danh sách KH có đơn hàng mới
    </button>

    <button class="btn btn-sm btn-warning" id="btn-get-list-depositting-customer">
        Lấy danh sách KH có đơn hàng đã cọc - đang đặt
    </button>
    <button class="btn btn-sm btn-danger" id="btn-close-list">Ẩn</button>
    <br> <br>
    <div id="table-customer-new-order" class="row"></div>
</div>
@endif

<script>
    $( document ).ready(function() {
        $('#btn-get-list-customer').on('click', function () {
            $.ajax({
                url: "purchase_orders/get-list-customer-new-order",
                type: 'GET',
                dataType: "JSON",
                success: function (response)
                {
                    if (response.status && response.html != "") {
                        $('#table-customer-new-order').children().remove();

                        console.log(response.html);
                        $('#table-customer-new-order').append(response.html);
                    }
                }
            });
        });

        $('#btn-close-list').on('click', function () {
            $('#table-customer-new-order').children().remove();
        });

        $('#btn-get-list-depositting-customer').on('click', function () {
            $.ajax({
                url: "purchase_orders/get-list-customer-depositting-order",
                type: 'GET',
                dataType: "JSON",
                success: function (response)
                {
                    if (response.status && response.html != "") {
                        $('#table-customer-new-order').children().remove();

                        console.log(response.html);
                        $('#table-customer-new-order').append(response.html);
                    }
                }
            });
        });
    });
</script>