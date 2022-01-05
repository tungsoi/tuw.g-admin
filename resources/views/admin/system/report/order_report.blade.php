<table class="table table-bordered">
    <thead>
        <th style="background: wheat !important">NVĐH</th>
        <th style="background: wheat !important">Số lượng đơn</th>
        <th style="background: wheat !important">Tổng tiền đơn hàng</th>
        <th style="background: wheat !important">Tổng tiền thanh toán</th>
        <th style="background: wheat !important">Tổng phí dịch vụ</th>
        <th style="background: wheat !important">Tổng đàm phán tệ</th>
        <th style="background: wheat !important">Tổng đàm phán VND</th>
        <th style="background: wheat !important">Tổng thực đặt</th>
    </thead>
    <tbody>
        @if ($data != null)
            @foreach ($data as $user)
                <tr>
                    <td>{{ $user->user_name }}</td>
                    <td>{{ $user->number }}</td>
                    <td>{{ $user->amount }}</td>
                    <td>{{ $user->final_payment }}</td>
                    <td>{{ $user->percent_service }}</td>
                    <td>{{ $user->offer_cn }}</td>
                    <td>{{ number_format($user->offer_vn) }}</td>
                    <td>{{ $user->total }}</td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="8"><i>Không có dữ liệu</i></td>
            </tr>
        @endif
    </tbody>
</table>