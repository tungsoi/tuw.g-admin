<a class='btn btn-sm btn-primary' href="{{ route('admin.purchase_orders.admin_deposite', $id) }}">1. Vào đặt cọc</a>

<a class='btn-update-status btn btn-sm btn-info' 
    target='_blank' 
    data-type="ordered" 
    data-title="Xác nhận chốt đã đặt hàng" 
    data-pk="{{ $id}}"
    data-action="{{ route('admin.purchase_orders.confirm_ordered') }}"
    >2. Chốt đã đặt hàng</a>

<a class='btn-update-status btn btn-sm btn-warning' 
    target='_blank' 
    data-type="vn-recevice" 
    data-title="Xác nhận chốt đã về Việt Nam" 
    data-pk="{{ $id}}"
    data-action="{{ route('admin.purchase_orders.confirm_ordered') }}">3. Chốt đã về Việt Nam</a>

{{-- @if ($status != 9)  --}}
<a class='btn-update-status btn btn-sm btn-success'
    target='_blank' 
    data-type="success" 
    data-title="Xác nhận chốt thành công" 
    data-pk="{{ $id}}"
    data-action="{{ route('admin.purchase_orders.confirm_ordered') }}">4. Chốt thành công</a>
{{-- @endif --}}

{{-- @if ($payment_route == "#")
    <a class='btn btn-sm btn-danger' id="btn-payment-error">5. Thanh toán</a>
@else
    <a class='btn btn-sm btn-danger' target='_blank'  href="{{ $payment_route }}">5. Thanh toán - (Tiền {{ $tips }})</a>
@endif --}}

<script>
    $('.btn-update-status').click(function (e) {
        let iThis = $(this);
        let iType = iThis.data('type');
        let iTitle = iThis.data('title');
        let iPk = iThis.data('pk');
        e.preventDefault();
        
        swal({
            title: iTitle,
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
                      'id': iPk,
                      'type': iType
                    },
                    success: function (response)
                    {
                        if (response.status) {
                            $('.loading-overlay').toggle();
                            $.admin.toastr.success(response.message, '', {timeOut: 10000});
                            location.reload();
                        } else {
                            $('.loading-overlay').toggle();
                            $.admin.toastr.error(response.message, '', {timeOut: 10000});
                        }
                    }
                }); 
            }
        });
    });

    $("#btn-payment-error").on('click', function () {
        $.admin.toastr.error('Không có Mã vận đơn nào đã về kho Việt Nam để thanh toán.', '', {timeOut: 10000});
    });
</script>