<table class="table table-bordered" id="tbl-payment">
    <thead>
        <tr><th style="width: 25%"></th>
        <th style="width: 25%">Số lượng</th>
        <th style="widht: 25%">Đơn giá (Tệ)</th>
        <th style="width: 25%">Thành tiền (Tệ)</th>
    </tr></thead>
    <tbody>
        <tr>
            <td align="center">Tổng khối lượng (kg)</td>
            <td align="right">
                <span class="lb-sum-kg" name="lb-sum-kg">0</span>
                <input type="hidden" name="count-kg" class="count-kg" value="">
            </td>
            <td>
                <input type="text" name="sum_kg" class="form-control sum_kg" placeholder="Nhập đơn giá kg" value="">
            </td>
            <td align="right">
                <input type="text" name="total_kg" class="form-control total_kg" placeholder="Thành tiền kg" readonly="" value="">
            </td>
        </tr>
        <tr>
            <td align="center">Tổng V/6000</td>
            <td align="right">
                <span class="lb-sum-volumn" name="lb-sum-volumn">0</span>
                <input type="hidden" name="count-volumn" class="count-volumn" value="">
            </td>
            <td>
                <input type="text" name="sum_volumn" class="form-control sum_volumn" placeholder="Nhập đơn giá V/6000" value="">
            </td>
            <td align="right">
                <input type="text" name="total_volumn" class="form-control total_volumn" placeholder="Thành tiền V/6000" readonly="">
            </td>
        </tr>
        <tr>
            <td align="center">Tổng mét khối (m3)</td>
            <td align="right">
                <span class="lb-sum-cublic-meter" name="lb-sum-cublic-meter">0</span>
                <input type="hidden" name="count-cublic-meter" class="count-cublic-meter" value="">
            </td>
            <td>
                <input type="text" name="sum_cublic_meter" class="form-control sum_cublic_meter" placeholder="Nhập đơn giá mét khối" value="">
            </td>
            <td align="right">
                <input type="text" name="total_cublic_meter" class="form-control total_cublic_meter" placeholder="Thành tiền mét khối" readonly="">
            </td>
        </tr>
        <tr>
            <td colspan="3" align="center">Tổng ứng kéo</td>
            <td>
                <span class="advan" name="advan[]"></span>
                <input type="hidden" name="advan" class="advan">
            </td>
        </tr>
        <tr>
            <td colspan="3" align="center">Tổng tiền</td>
            <td>
                <span class="total_money" name="total_money[]"></span>
                <input type="hidden" name="total-money" class="total-money">
            </td>
        </tr>
    </tbody>
</table>