<table class="table table-bordered">
    <thead>
        <th>Tổng tiền theo KG</th>
        <th>Tổng tiền theo M3</th>
        <th>Tổng tiền đầu ra</th>
    </thead>
    <tbody>
        <tr>
            <td style="color: blue">{{ number_format($amount_output['kg']) }}</td>
            <td style="color: red">{{ number_format($amount_output['m3']) }}</td>
            <td style="color: green">{{ number_format($amount_output['amount']) }}</td>
        </tr>
    </tbody>
</table>