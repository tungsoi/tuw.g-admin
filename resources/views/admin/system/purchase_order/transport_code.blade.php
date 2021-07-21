<ul style="padding-left: 15px;" class="list-transport-code-{{ $id }}">
    @foreach ($transport_code as $key   =>  $row)
        <li><a href="#">{{ $row }}</a></li>
    @endforeach
</ul>

<button class="btn btn-xs btn-success btn-add-transport-code" data-index="{{ $id }}">
    <i class="fa fa-plus"></i>
</button>

<div id="mdl-add-transport-code-{{ $id }}" class="modal" role="dialog">
    <div class="modal-dialog">
  
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Thêm mã vận đơn</h4>
        </div>
        <div class="modal-body">

            <p>Mã đơn hàng: {{ $order_number }}</p>
            <input type="hidden" name="order_id" value="{{ $id }}" class="order-id-{{ $id }}">
            <input type="text" name="transport_code" class="transport-code-{{ $id }} form-control" placeholder="Nhập mã vận đơn">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-success btn-xs btn-save-transport-code" data-index="{{ $id }}">Lưu lại</button>
            <button type="button" class="btn btn-default btn-xs" data-dismiss="modal">Huỷ bỏ</button>
        </div>
      </div>
  
    </div>
</div>

<input type="hidden" class="append-route" value="{{ route('admin.purchase_orders.addTransportCode') }}">

<script>
    $('.btn-add-transport-code').on('click', function () {
        let index = $(this).data('index');
        $('#mdl-add-transport-code-' + index).modal('show');
    });

    let flag = false;
    $(".btn-save-transport-code").on('click', function (e) {
        e.preventDefault();
        let index = $(this).data('index');
        let order_id =  $('.order-id-' + index).val();
        let transport_code = $('.transport-code-' + index).val();

        if (! flag) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            $.ajax({
                url: $('.append-route').val(),
                type: 'POST',
                dataType: "JSON",
                data: {
                    'id': order_id,
                    'transport_code': transport_code
                },
                success: function (response)
                {
                    $.admin.toastr.success(response.message, '', {positionClass: 'toast-top-center'});
                    flag = true;
                }
            });
        }
        
    });
</script>