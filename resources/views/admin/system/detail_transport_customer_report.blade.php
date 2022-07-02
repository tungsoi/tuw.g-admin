<table class="table table-bordered">
    <thead>
        <th style="background-color: wheat !important;">TIÊU ĐỀ</th>
        <th style="background-color: wheat !important;">SỐ LƯỢNG ĐƠN</th>
        <th style="background-color: wheat !important;">TỔNG CÂN (KG)</th>
        <th style="background-color: wheat !important;">TỔNG KHỐI (M3)</th>
        <th style="background-color: wheat !important;">TỔNG DOANH THU (VND)</th>
    </thead>
    <tbody>
        <tr>
            <td>
                <ul>
                    <li>{{ $report->title }}</li>
                    <li>Bắt đầu: {{ $report->begin }}</li>
                    <li>Kết thúc: {{ $report->finish }}</li>
                </ul>
            </td>
            <td>{{ $data->sum('count') }}</td>
            <td>{{ number_format($data->sum('kg'), 1) }}</td>
            <td>{{ number_format($data->sum('m3'), 3) }}</td>
            <td>{{ number_format($data->sum('amount') - $data->sum('advance_drag')) }}</td>
        </tr>
    </tbody>
</table>