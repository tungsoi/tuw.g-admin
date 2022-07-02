<table class="table table-bordered">
    <thead>
        <th>Mã khách hàng</th>
        <th>Tháng</th>
        <th>Số đơn</th>
        <th>Tổng KG</th>
        <th>Tổng M3</th>
        <th>Tổng doanh thu</th>
    </thead>
    <tbody>
        @foreach ($temp as $row)
            <tr>
                <td>{{ $row['symbol_name'] }}</td>
                <td>{{ $row['title'] }}</td>
                <td>{{ $row['count'] }}</td>
                <td>{{ $row['kg'] }}</td>
                <td>{{ $row['m3'] }}</td>
                <td>{{ $row['amount'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>