<a href="#" class="customer-recharge-{{ $id }} btn btn-xs btn-success" data-toggle="tooltip" title="Nạp tiền" data-id="{{ $id }}">
    <i class="fa fa-dollar"></i>
</a>

<div id="mdl-customer-recharge-{{ $id }}" class="modal" role="dialog">
    <div class="modal-dialog modal-lg">

        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title pull-left">Nạp tiền - {{ $customer->symbol_name }} - Số dư: {{ number_format($customer->wallet )}} (VND)</h4>
                <br>
            </div>
            <div class="modal-body" style="text-align: left">
                {{-- <form action="" method="post">
                    <div class="form-group">
                        <label for="type">Loại giao dịch</label>
                        <select class="form-control" name="type_recharge" id="">
                            @foreach ($type as $row)
                                <option value="{{ $row->id }}">{{ $row->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="pwd">Số tiền</label>
                        <input type="password" class="form-control" id="pwd">
                    </div>
                    <div class="form-group">
                        <label for="note">Ghi chú</label>
                        <input type="text" class="form-control" id="note" name="note">
                    </div>
                    <button type="submit" class="btn btn-success btn-sm" style="width: 80px;">Thực hiện</button>
                    <button type="button" class="btn btn-danger btn-sm" style="width: 80px;" onclick="window.location.reload()" data-dismiss="modal">Huỷ bỏ</button>
                </form> --}}

                {!! $form !!}
            </div>
        </div>

    </div>
</div>