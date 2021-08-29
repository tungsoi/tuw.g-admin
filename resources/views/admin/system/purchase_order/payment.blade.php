<br>
<table class="table table-bordered">
    <thead>
        <th>STT</th>
        <th>Mã vận đơn</th>
        <th>Mã khách hàng</th>
        <th>Loại thanh toán</th>
        <th>KG</th>
        <th>Dài</th>
        <th>Rộng</th>
        <th>Cao</th>
        <th>M3</th>
        <th>V/6000</th>
        <th>Ứng kéo</th>
    </thead>
    <tbody>
        @foreach ($transportCodes as $key => $code)
            <tr>
                <td align="center">{{ $key+1 }}</td>
                <td style="width: 300px;">{{ $code->transport_code }}</td>
                <td>{{ $code->customer_code_input }}</td>
                <td>
                    <select class=" payment_type" style="width: 100%;">
                        <option value="1" selected="">Khối lượng</option>
                        <option value="0">V/6000</option>
                        <option value="-1">M3</option>
                    </select>
                </td>
                <td align="right">{{ $code->kg }}</td>
                <td align="right">{{ $code->length }}</td>
                <td align="right">{{ $code->width }}</td>
                <td align="right">{{ $code->height }}</td>
                <td align="right">{{ $code->m3() }}</td>
                <td align="right">{{ $code->v() }}</td>
                <td align="right">{{ $code->advance_drag }}</td>
            </tr>
        @endforeach
    </tbody>
</table>