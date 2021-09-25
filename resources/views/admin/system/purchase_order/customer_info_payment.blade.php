<table class="table table-bordered" id="customer-info-payment">
    <thead>
        <th colspan="5" style="text-align: left">Thông tin khách hàng</th>
    </thead>
    <tbody>
        <tr>
            <td>Số dư ví</td>
            <td>Ví cân</td>
            <td>Giá cân</td>
            <td>Giá khối</td>
            <td>Ghi chú</td>
        </tr>
        <tr>
            <td>
                <input type="hidden" name="customer_id" class="customer_select_id" value="">
                <span id="payment_customer_wallet">0</span>
            </td>
            <td>
                <input type="hidden" name="payment_customer_wallet_weight" class="payment_customer_wallet_weight" value="0">
                <input type="hidden" name="payment_customer_wallet_weight_used" class="payment_customer_wallet_weight_used" value="0">
                <span id="payment_customer_wallet_weight">0</span>
            </td>
            <td><span id="price_kg">0</span></td>
            <td><span id="price_m3">0</span></td>
            <td><span id="customer_note"></span></td>
        </tr>
    </tbody>
</table>