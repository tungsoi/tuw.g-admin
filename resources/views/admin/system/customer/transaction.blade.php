@if ($empty)
    <style>
        .column-__actions__ {
            display: none;
        }
    </style>
@endif
<div class="row">
    <div class="col-md-3">
        <div class="alert alert-warning">
            <h4>Mã khách hàng</h4>
            <b>{{ $customer->symbol_name }}</b>
        </div>
    </div>
    <div class="col-md-3">
        <div class="alert alert-info">
            <h4>Số dư ví hiện tại</h4>
            <b>{{ number_format($customer->wallet) }} VND</b>
        </div>
    </div>
    <div class="col-md-3">
        <div class="alert alert-success">
            <h4>Số giao dịch</h4>
            <b>
                {{-- @if (is_array($data) && sizeof($data) > 0)
                    {{ sizeof($data) }}
                @else
                    0
                @endif --}}
                {{ $number ?? 0 }}
            </b>
        </div>
    </div>
    <div class="col-md-3">
        <div class="alert alert-danger">
            <h4>Giao dịch gần nhất</h4>
            <b>
                @if (is_array($data) && sizeof($data) > 0)
                    {{ $data[0]['payment_date'] }}
                @else
                    0
                @endif
            </b>
        </div>
    </div>
</div>
<br>

@if ( (Admin::user()->isRole('ar_employee') || Admin::user()->isRole('warehouse_employee')) && isset($mode) && $mode == 'recharge' && $form != "")
    {!! $form !!}
@endif

<table class="table table-bordered">
    <thead>
        <th>STT</th>
        <th>Ngày giao dịch</th>
        <th>Người tạo</th>
        <th>Cập nhật</th>
        <th>Người sửa</th>
        <th>Đơn hàng</th>
        <th>Loại giao dịch</th>
        <th>Nội dung giao dịch</th>
        <th>Số dư đầu kỳ (VND)</th>
        <th>Trừ tiền (VND)</th>
        <th>Nạp tiền (VND)</th>
        <th>Số dư cuối kỳ (VND)</th>

        @if (! isset($disableAction))
            <th>Thao tác</th>
        @endif
    </thead>
    <tbody>
        {{-- @if (is_array($data) && sizeof($data) > 0) --}}
        @foreach ($data as $transaction)
            <tr
                @if (isset($transactionId) && $transactionId != "" && $transactionId == $transaction['id'])
                    style="background: wheat"
                @endif
            >
                <td align="center">{{ $transaction['order'] }}</td>
                <td align="center">{{ $transaction['payment_date'] }}</td>
                <td>{{ $transaction['user_id_created'] }}</td>
                <td>{{ date('H:i | d-m-Y', strtotime($transaction['updated_at'])) }}</td>
                <td>{{ $transaction['updated_user_id'] }}</td>
                <td>
                    @if ($transaction['payment_order_id'] != "")
                        <a href="{{ route('admin.payments.showRebuild', $transaction['payment_order_id']) }}">{{ $transaction['payment_order'] }}</a>
                    @endif
                </td>
                <td align="center">{{ $transaction['type_recharge'] }}
                    @if ($transaction['type_recharge'] == "Nạp tiền chuyển khoản")
                        <span style="color: red">{{ $transaction['bank'] }}</span>
                    @endif
                </td>
                <td>{{ $transaction['content'] }}</td>
                <td align="right">{!! $transaction['before_payment'] !!}</td>
                <td align="right">{!! $transaction['down'] !!}</td>
                <td align="right">{!! $transaction['up'] !!}</td>
                <td align="right">{!! $transaction['after_payment'] !!}</td>

                @if (! isset($disableAction))
                    <td class="actions">

                        @if (Admin::user()->isRole('ar_employee'))
                            @php
                                $route = route('admin.customers.transactions', $customer->id) . "?mode=recharge&transaction_id=" . $transaction['id'];
                            @endphp
                            <a href="{{ $route }}" class="grid-row-edit btn btn-xs btn-warning" data-toggle="tooltip" title="" data-original-title="Chỉnh sửa">
                                <i class="fa fa-edit"></i>
                            </a>

                            <a href="javascript:void(0);" data-url="{{ route('admin.transactions.destroy', $transaction['id']) }}" data-id="{{ $transaction['id'] }}" class="grid-row-custom-delete btn btn-xs btn-danger" data-toggle="tooltip" title="Xóa">
                                <i class="fa fa-trash"></i>
                            </a>
                        @endif
                    </td>
                @endif
            </tr>
        @endforeach
        {{-- @endif --}}
    </tbody>
</table>

{{ $data->links() }}

{{-- {{ $data->links() }} --}}

<script>
    $('.grid-row-custom-delete').on('click', function () {

        let url = $(this).data('url');
        let id = $(this).data('id');

        Swal.fire({
            title: 'Bạn có chắc chắn muốn xoá?',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Đồng ý',
            cancelButtonText: 'Huỷ bỏ'
        }).then((result) => {
            if (result.value == true && result.dismiss == undefined) {

                $('.loading-overlay').show();
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax(
                {
                    url: url,
                    type: 'delete', // replaced from put
                    dataType: "JSON",
                    success: function (response)
                    {
                        if (response.isRedirect) {
                            setTimeout(function () {
                                window.location.href = response.url;
                            }, 1000);
                        } else {
                            setTimeout(function () {
                                window.location.reload();
                            }, 1000);
                        }
                        
                    }
                });
            }
        })

    });

    $( document ).ready(function() {

        let ul_pagination = $('ul.pagination');
        ul_pagination.find('li').first().remove();
        ul_pagination.find('li').last().remove();

        let li_element = ul_pagination.find('li');

        li_element.each(function (e) {
            if (! li_element.eq(e).hasClass('active')) {
                let link = li_element.eq(e).find('a');
                let page = link.html();

                let full_url = document.URL
                let url = "";
                if (full_url.indexOf("?") !== -1) {
                    url = document.URL + "&page=" + page;
                } else {
                    url = document.URL + "?page=" + page;
                }
               
                link.attr('href', url);
            }
        });
    });
</script>