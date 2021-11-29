<style>
    ul {
        list-style-type: none;
        padding: 0;
    }
    .table_wrapper{
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }
    .text-red {
        color: red;
        font-weight: 600;
    }
</style>
<div class="table_wrapper">
<table class="table table-bordered">
    <thead>
        <th>STT</th>
        <th>Nhân viên</th>
        <th>Khách hàng</th>
        <th>Đơn hàng order hoàn thành</th>
        <th>Đơn hàng order chưa hoàn thành</th>
        <th>Đơn hàng vận chuyển</th>
        <th>Tổng số cuối</th>
    </thead>
    <tbody>
        @foreach ($data as $key => $value)
        <tr>
            <td>{{ $key+1 }}</td>
            <td>
                <ul data-note="Thông tin nhân viên">
                    <li class="employee_name">{{ $value->employee->name }}</li>
                    <li class="joining_date">{{ date('d/m/Y', strtotime($value->employee->created_at)) }}</li>
                </ul>
            </td>
            <td>
                <ul data-note="Thông tin khách hàng">
                    <li class="new_customer">KH mới <span class="pull-right">{{ $value->new_customer }}</span></li>
                    <li class="old_customer">KH cũ <span class="pull-right">{{ $value->old_customer }}</span></li>
                    <li class="all_customer">Tổng số <span class="pull-right">{{ $value->all_customer }}</span></li>
                    <li class="owed_wallet_new_customer">Công nợ KH mới <span class="pull-right">{{ number_format($value->owed_wallet_new_customer) }}</span></li>
                    <li class="owed_wallet_old_customer">Công nợ KH cũ <span class="pull-right">{{ number_format($value->owed_wallet_old_customer) }}</span></li>
                    <li class="owed_wallet_all_customer">Công nợ tổng <span class="pull-right text-red">{{ number_format( $value->owed_wallet_all_customer) }}</span></li>
                </ul>
            </td>
            <td>
                <ul data-note="Đơn order thành công">
                    <li class="po_success">Số lượng <span class="pull-right">{{ $value->po_success }}</span></li>
                    <li class="po_success_new_customer">Doanh số KH mới <span class="pull-right">{{ number_format($value->po_success_new_customer) }}</span></li>
                    <li class="po_success_old_customer">Doanh số KH cũ <span class="pull-right">{{ number_format($value->po_success_old_customer) }}</span></li>
                    <li class="po_success_all_customer">Tổng doanh số <span class="pull-right text-red">{{ number_format($value->po_success_all_customer) }}</span></li>
                    <li class="po_success_service_fee">Phí dịch vụ <span class="pull-right text-red">{{ number_format($value->po_success_service_fee) }}</span></li>
                    <li class="po_success_total_rmb">Tổng giá tệ <span class="pull-right text-red">{{ number_format($value->po_success_total_rmb) }}</span></li>
                    <li class="po_success_offer">Tổng đàm phán <span class="pull-right text-red">{{ number_format($value->po_success_offer) }}</span></li>
                </ul>
            </td>
            <td>
                <ul data-note="Đơn order chưa hoàn thành">
                    <li class="po_not_success">Số lượng <span class="pull-right">{{ $value->po_not_success }}</span></li>
                    <li class="po_not_success_new_customer">Doanh số KH mới <span class="pull-right">{{ number_format($value->po_not_success_new_customer) }}</span></li>
                    <li class="po_not_success_old_customer">Doanh số KH cũ <span class="pull-right">{{ number_format($value->po_not_success_old_customer) }}</span></li>
                    <li class="po_not_success_all_customer">Tổng doanh số <span class="pull-right  text-red">{{ number_format($value->po_not_success_all_customer) }}</span></li>
                    <li class="po_not_success_service_fee">Phí dịch vụ <span class="pull-right">{{ number_format($value->po_not_success_service_fee) }}</span></li>
                    <li class="po_not_success_owed">Tổng tiền đã cọc <span class="pull-right">{{ number_format($value->po_not_success_deposited) }}</span></li>
                    <li class="po_not_success_owed">Công nợ trên đơn <span class="pull-right  text-red">{{ number_format($value->po_not_success_owed) }}</span></li>
                </ul>
            </td>
            <td>
                <ul data-note="Đơn hàng vận chuyển">
                    <li class="transport_order">Số lượng <span class="pull-right">{{ $value->transport_order }}</span></li>
                    <li class="trs_kg_new_customer">Số KG KH mới <span class="pull-right">{{ $value->trs_kg_new_customer }}</span></li>
                    <li class="trs_kg_old_customer">Số KG KH cũ <span class="pull-right">{{ $value->trs_kg_old_customer }}</span></li>
                    <li class="trs_kg_all_customer">Tổng KG <span class="pull-right text-red">{{ $value->trs_kg_all_customer }}</span></li>
                    <li class="trs_m3_new_customer">Số M3 KH mới <span class="pull-right">{{ $value->trs_m3_new_customer }}</span></li>
                    <li class="trs_m3_old_customer">Số M3 KH cũ <span class="pull-right">{{ $value->trs_m3_old_customer }}</span></li>
                    <li class="trs_m3_all_customer">Tổng M3 <span class="pull-right text-red">{{ $value->trs_m3_all_customer }}</span></li>
                    <li class="trs_amount_new_customer">Doanh thu KH mới <span class="pull-right">{{ number_format($value->trs_amount_new_customer) }}</span></li>
                    <li class="trs_amount_old_customer">Doanh thu KH cũ <span class="pull-right">{{ number_format($value->trs_amount_old_customer) }}</span></li>
                    <li class="trs_amount_all_customer">Tổng doanh thu <span class="pull-right text-red">{{ number_format($value->trs_amount_all_customer) }}</span></li>
                </ul>
            </td>
            <td>
                <ul data-note="Tổng kết">
                    <li class="po_success">Số đơn thành công <span class="pull-right">{{ $value->po_success }}</span></li>
                    <li class="po_success_service_fee">Doanh thu phí dịch vụ (100%) <span class="pull-right">{{ number_format($value->po_success_service_fee) }}</span></li>
                    <li class="trs_amount_all_customer">Doanh thu vận chuyển (10%) <span class="pull-right">{{ number_format($value->trs_amount_all_customer*0.1) }}</span></li>
                    <li class="po_success_total_rmb">Doanh thu tỷ giá (30 * Tổng giá tệ) <span class="pull-right">{{ number_format($value->po_success_total_rmb*30) }}</span></li>
                    <li class="po_success_offer">Doanh thu đàm phán (85%) <span class="pull-right">{{ number_format($value->po_success_offer*0.85) }}</span></li>
                    <li class="total_revenue">
                        Tổng doanh thu
                        @php
                            $total = $value->po_success_service_fee + ($value->trs_amount_all_customer*0.1) + ($value->po_success_total_rmb*30) + ($value->po_success_offer*0.85);
                        @endphp
                        <span class="pull-right text-red">{{ number_format($total) }}</span>
                    </li>
                </ul>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
</div>