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
        @php
            $user_name = $number = $amount = $final_payment = $percent_service = $offer_cn = $offer_vn = $total = $index = 0;
        @endphp
        @if ($data != null)
            @foreach ($data as $key => $user)
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
                @php
                    $index ++;
                    $number += $user->number;
                    $amount += $user->amount;
                    $final_payment += $user->final_payment;
                    $percent_service += $user->percent_service;
                    $offer_cn += $user->offer_cn;
                    $offer_vn += $user->offer_vn;
                    $total += $user->total;
                @endphp
            @endforeach
            <tr>
                <td style="background: lavender !important">Tổng: {{ $index }}</td>
                <td style="background: lavender !important">{{ $number }}</td>
                <td style="background: lavender !important">{{ $amount }}</td>
                <td style="background: lavender !important">{{ $final_payment }}</td>
                <td style="background: lavender !important">{{ $percent_service }}</td>
                <td style="background: lavender !important">{{ $offer_cn }}</td>
                <td style="background: lavender !important">{{ number_format($offer_vn) }}</td>
                <td style="background: lavender !important">{{ $total }}</td>
            </tr>
        @else
            <tr>
                <td colspan="8"><i>Không có dữ liệu</i></td>
            </tr>
        @endif
    </tbody>
</table>