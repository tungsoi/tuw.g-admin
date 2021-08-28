<h4 style="color: #464646; font-weight: bold">Sản phẩm trong đơn đã được đặt hàng hết. Xác nhận chốt đã đặt hàng cả đơn ?</h4>

<button class="btn btn-sm btn-success pull-left" id="btn-confirm-ordered" data-action="{{ route('admin.purchase_orders.confirm_ordered') }}" data-pk="{{ $id }}">
    <i class="fa fa-check"></i>
    Đồng ý
</button>

<script>

    $('#btn-confirm-ordered').click(function (e) {
        let iThis = $(this);
        e.preventDefault();
        
        swal({
            title: "Xác nhận chốt đã đặt hàng ?",
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