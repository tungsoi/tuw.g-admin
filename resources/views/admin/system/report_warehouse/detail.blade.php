<table class="table table-bordered">
    <thead>
        <tr>
            <th>Ngày về kho</th>
            <th>Thực nhận</th>
            <th>Cân nặng</th>
            <th>Mét khối</th>
        </tr>
    </thead>
    <tbody>
        @php
            $count = $kg = $m3 = 0;
        @endphp
        @foreach ($date as $date_row)
            <tr>
                <td>{{ $date_row }}</td>
                <td>{{ $temp[$date_row]['count'] }}</td>
                <td>{{ $temp[$date_row]['kg'] }}</td>
                <td>{{ $temp[$date_row]['m3'] }}</td>
            </tr>
            @php
                $count += $temp[$date_row]['count'];
                $kg += $temp[$date_row]['kg'];
                $m3 += $temp[$date_row]['m3'];
            @endphp
        @endforeach

        <tr style="background: wheat">
            <td>Tổng</td>
            <td>{{ $count }}</td>
            <td>{{ $kg }}</td>
            <td>{{ $m3 }}</td>
        </tr>
    </tbody>
</table>