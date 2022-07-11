<table class="table table-bordered">
    <thead>
        <th style="background-color: wheat !important;">TIÊU ĐỀ</th>
        <th style="background-color: wheat !important;">SỐ LƯỢNG ĐƠN</th>
        <th style="background-color: wheat !important;">TỔNG TIỀN SẢN PHẨM (VND)</th>
        <th style="background-color: wheat !important;">TỔNG PHÍ DỊCH VỤ (VND)</th>
        <th style="background-color: wheat !important;">TỔNG TIỀN VẬN CHUYỂN NỘI ĐỊA (VND)</th>
        <th style="background-color: wheat !important;">TỔNG GIÁ CUỐI (VND)</th>
    </thead>
    <tbody>
        <tr>
            <td>
                <ul>
                    <li>{{ $report->title }}</li>
                    <li>Bắt đầu: {{ $report->begin }}</li>
                    <li>Kết thúc: {{ $report->finish }}</li>
                    <li>Cập nhật gần nhất: {{ $report->updated_at }}</li>
                </ul>
            </td>
            <td>{{ number_format($report->details->sum('count')) }}</td>
            <td>{{ number_format($report->details->sum('total_price_items')) }}</td>
            <td>{{ number_format($report->details->sum('total_service')) }}</td>
            <td>{{ number_format($report->details->sum('total_ship')) }}</td>
            <td>{{ number_format($report->details->sum('total_amount')) }}</td>
        </tr>
    </tbody>
</table>