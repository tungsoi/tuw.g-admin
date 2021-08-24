<button class="btn btn-sm btn-warning pull-right" id="btn-customer-deposite" data-action="{{ route('admin.purchase_orders.customer_deposite') }}" data-pk="{{ $id }}">
    <i class="fa fa-money"></i> &nbsp;
    Đặt cọc bằng số dư
</button>

<script>

    $('#btn-customer-deposite').click(function (e) {
        let iThis = $(this);
        e.preventDefault();
        
        swal({
            title: "Bạn có muốn đặt cọc đơn hàng này bằng số dư tài khoản ?",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Đồng ý",
            cancelButtonText: "Huỷ bỏ",
        }).then(function (result) {
            if (result.value) {
                $('.loading-overlay').toggle();

                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                
                $.ajax({
                    url: iThis.data("action"),
                    type: 'POST',
                    dataType: "JSON",
                    data: {
                      'id': iThis.data('pk')  
                    },
                    success: function (response)
                    {
                        if (response.status) {
                            $('.loading-overlay').toggle();
                            $.admin.toastr.success(response.message, '', {timeOut: 10000});
                            setTimeout(function () {
                                location.reload();
                            }, 1000);
                        } else {
                            $('.loading-overlay').toggle();
                            $.admin.toastr.error(response.message, '', {timeOut: 10000});
                        }
                    }
                }); 
            }
        });
    });
</script>