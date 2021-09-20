<a class="btn btn-xs btn-info btn-edit-transport-code" 
style="width: auto !important;"
data-toggle="modal" data-target="#myModal-{{ $order->id }}"data-pk="{{ $order->id }}">
    Sửa MVD
</a>

<!-- Modal -->
<div id="myModal-{{ $order->id }}" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <form action="{{ route('admin.purchase_orders.updateTransportCode') }}" method="post">
        {{ csrf_field() }}
        <!-- Modal content-->
        <div class="modal-content" style="text-align: left !important;">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Mã vận đơn trên đơn : {{ $order->order_number }}</h4>
        </div>
        <div class="modal-body">
            <input type="hidden" name="id" value="{{ $order->id }}">
            <textarea class="form-control" name="transport_code" id="" cols="30" rows="3">{{ $order->transport_code }}</textarea>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-success btn-sm" 
            style="width: auto !important;">Lưu lại</button>
            <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal" 
            style="width: auto !important;">Huỷ bỏ</button>
        </div>
        </div>
    </form>

  </div>
</div>