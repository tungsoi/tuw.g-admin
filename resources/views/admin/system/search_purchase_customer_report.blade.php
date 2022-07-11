<table class="table table-bordered">
    <thead>
        <th>Mã khách hàng</th>
        <th>Tháng</th>
        <th>Số đơn</th>
        <th>Tổng giá sản phẩm</th>
        <th>Tổng phí dịch vụ</th>
        <th>Tổng phí vận chuyển nội địa</th>
        <th>Tổng giá cuối</th>
    </thead>
    <tbody>
        @foreach ($temp as $row)
            <tr>
                <td>{{ $row['symbol_name'] }}</td>
                <td>{{ $row['title'] }}</td>
                <td>{{ $row['count'] }}</td>
                <td>{{ $row['total_price_items'] }}</td>
                <td>{{ $row['total_service'] }}</td>
                <td>{{ $row['total_ship'] }}</td>
                <td>{{ $row['total_amount'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>