<table class="table table-bordered" id="tbl-payment" style="font-size: 14px;">
    <thead>
        <tr><th style="width: 25%"></th>
        <th style="width: 25%">Số lượng</th>
        <th style="widht: 25%">Đơn giá (VND)</th>
        <th style="width: 25%">Thành tiền (VND)</th>
    </tr></thead>
    <tbody>
        <tr>
            <td align="center"><b>Tổng khối lượng (kg)</b></td>
            <td align="right">
                <span class="lb-sum-kg" name="lb-sum-kg">{{ $amount_kg }}</span>
                <input type="hidden" name="count_kg" class="count_kg" value="{{ $amount_kg }}">
            </td>
            <td>
                <input type="text" name="sum_kg" class="form-control sum_kg" placeholder="Nhập đơn giá kg" value="0">
            </td>
            <td align="right">
                <input type="text" name="total_kg" class="form-control total_kg" placeholder="Thành tiền kg" readonly="" value="">
            </td>
        </tr>
        <tr>
            <td align="center"><b>Tổng mét khối (m3)</b></td>
            <td align="right">
                <span class="lb-sum-cublic-meter" name="lb-sum-cublic-meter">0</span>
                <input type="hidden" name="count_cublic_meter" class="count_cublic_meter" value="">
            </td>
            <td>
                <input type="text" name="sum_cublic_meter" class="form-control sum_cublic_meter" placeholder="Nhập đơn giá mét khối" value="0">
            </td>
            <td align="right">
                <input type="text" name="total_cublic_meter" class="form-control total_cublic_meter" placeholder="Thành tiền mét khối" readonly="">
            </td>
        </tr>
        <tr>
            <td align="center"><b>Tổng V/6000</b></td>
            <td align="right">
                <span class="lb-sum-volumn" name="lb-sum-volumn">0</span>
                <input type="hidden" name="count_volumn" class="count_volumn" value="">
            </td>
            <td>
                <input type="text" name="sum_volumn" class="form-control sum_volumn" placeholder="Nhập đơn giá V/6000" value="0">
            </td>
            <td align="right">
                <input type="text" name="total_volumn" class="form-control total_volumn" placeholder="Thành tiền V/6000" readonly="">
            </td>
        </tr>
        @php
            $total_money = 0;

            if ($amount_advance_drag > 0) {
                $total_money += $amount_advance_drag * $current_rate;
            }
        @endphp
        <tr>
            <td colspan="2" align="center"><b>Tổng ứng kéo</b></td>
            <td>
                <span class="advan_rmb" name="advan_rmb[]">{{ $amount_advance_drag }} (tệ)</span>
                <input type="hidden" name="advan_rmb" class="advan_rmb" value="{{ $amount_advance_drag }}">
            </td>
            <td>
                <span class="advan_vnd" name="advan_vnd[]">{{ number_format($amount_advance_drag * $current_rate) }}</span>
                <input type="hidden" name="advan_vnd" class="advan_vnd" value="{{ str_replace(",", "", number_format($amount_advance_drag * $current_rate, 0)) }}">
            </td>
        </tr>
        @if (isset($purchaseOrderData) && $purchaseOrderData != null)
            @php
                $money = (str_replace(",", "", $purchaseOrderData->amount()) * $purchaseOrderData->current_rate) - $purchaseOrderData->deposited;
                $total_money += $money;
            @endphp
            <tr>
                <td colspan="2" align="center"><b>Tiền mua hộ còn thiếu</b></td>
                <td></td>
                <td>
                    <span class="owed_purchase_order" name="owed_purchase_order[]">{{ number_format($money) }}</span>
                    <input type="hidden" name="owed_purchase_order" class="owed_purchase_order" value="{{ $money }}">
                    <input type="hidden" name="purchase_order_id" class="purchase_order_id" value="{{ $purchaseOrderData->id }}">
                </td>
            </tr>
        @endif
        <tr>
            <td colspan="3" align="center"><b>Tổng tiền</b></td>
            <td>
                <span class="total_money" name="total_money[]">{{ number_format($total_money) }}</span>
                <input type="hidden" name="total_money" class="total_money" value="{{ $total_money }}">
            </td>
        </tr>
    </tbody>
</table>