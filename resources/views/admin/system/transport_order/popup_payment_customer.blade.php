<div id="myModal" class="modal show" role="dialog">
    <div class="modal-dialog modal-md">
  
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Thông tin khách hàng thanh toán</h4>
        </div>
        <div class="modal-body">
            <table class="table table-bordered">
                <thead>
                    <th>Mã khách hàng</th>
                    <th>Số dư ví</th>
                    <th></th>
                </thead>
                <tbody>
                    <tr style="font-weight: bold; text-align: center;">
                        <td>{{ $customer->symbol_name }}</td>
                        <td>{{ number_format($customer->wallet) }} (VND)</td>
                        <td>
                            <a href="{{ route('admin.customers.transactions', $customer->id) }}" type="button" class="btn btn-primary btn-sm">Lịch sử ví</a> <br> <br>
                            <a href="{{ route('admin.customers.transactions', $customer->id) }}?mode=recharge" type="button" class="btn btn-success btn-sm">+ Nạp tiền</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="modal-footer">
          @if (isset($url) && $url != "")
            <a href="{{ $url }}" type="button" class="btn btn-danger btn-sm" >Đóng</a>
          @else
            <a href="{{ route('admin.transport_codes.index') }}" type="button" class="btn btn-danger btn-sm" >Đóng</a>
          @endif
        </div>
      </div>
  
    </div>
  </div>