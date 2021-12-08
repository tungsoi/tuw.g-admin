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
    <div class="row">
        <div class="col-md-12">
            <h5>{{ $report->title }}</h5>
            <h5>Thời gian cập nhật: <span class="text-red">{{ $data->last()->updated_at }}</span></h5>
            <h5>ĐƠN HÀNG ORDER HOÀN THÀNH: <span class="text-red">Tính theo ngày thành công trong tháng</span> </h5>
            <h5>ĐƠN HÀNG VẬN CHUYỂN: <span class="text-red">Tính theo đơn hàng xuất kho trong tháng</span></h5>
        </div>
    </div>
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
        <tr style="background: antiquewhite">
            <td>Tổng</td>
            <td>
                <ul>
                    <li>Nhân viên: <span class="pull-right">{{ $data->count() }}</span></li>
                </ul>
            </td>
            <td>
                <ul>
                    <li>Khách hàng: <span class="pull-right">{{ $data->sum('all_customer') }}</span></li>
                    <li>Tổng âm ví: <span class="pull-right text-red">{{ number_format($data->sum('owed_wallet_all_customer')) }}</span></li>
                </ul>
            </td>
            <td>
                <ul>
                    <li>Tổng đơn: <span class="pull-right">{{ $data->sum('po_success') }}</span></li>
                    <li>Tổng DS: <span class="pull-right text-red">{{ number_format($data->sum('po_success_all_customer')) }}</span></li>
                    <li>Tổng PDV: <span class="pull-right text-red">{{ number_format($data->sum('po_success_service_fee')) }}</span></li>
                    <li>Tổng đàm phán: <span class="pull-right text-red">{{ number_format($data->sum('po_success_offer')) }}</span></li>
                    <li>Tổng tệ: <span class="pull-right text-red">{{ number_format($data->sum('po_success_total_rmb')) }}</span></li>
                </ul>
            </td>
            <td>
                <ul>
                    <li>Tổng đơn: <span class="pull-right">{{ $data->sum('po_not_success') }}</span></li>
                    <li>Tổng DS: <span class="pull-right">{{ number_format($data->sum('po_not_success_all_customer')) }}</span></li>
                    <li>Tổng PDV: <span class="pull-right text-red">{{ number_format($data->sum('po_not_success_service_fee')) }}</span></li>
                    <li>Tổng cọc: <span class="pull-right">{{ number_format($data->sum('po_not_success_deposited')) }}</span></li>
                    <li>Tổng công nợ: <span class="pull-right text-red">{{ number_format($data->sum('po_not_success_owed')) }}</span></li>
                </ul>
            </td>
            <td>
                <ul>
                    <li>Tổng đơn: <span class="pull-right">{{ $data->sum('transport_order') }}</span></li>
                    <li>Tổng KG: <span class="pull-right">{{ $data->sum('trs_kg_all_customer') }}</span></li>
                    <li>Tổng M3: <span class="pull-right">{{ $data->sum('trs_m3_all_customer') }}</span></li>
                    <li>Tổng DT: <span class="pull-right text-red">{{ number_format($data->sum('trs_amount_all_customer')) }}</span></li>
                </ul>
            </td>
            <td>
                <ul>
                    <li class="po_success">Số đơn TC <span class="pull-right">{{ $data->sum('po_success') }}</span></li>
                    <li class="po_success_service_fee">Phí dịch vụ <i>(100%)</i> <span class="pull-right">{{ number_format($data->sum('po_success_service_fee')) }}</span></li>
                    <li class="trs_amount_all_customer">DT vận chuyển <i>(10%)</i><span class="pull-right">{{ number_format($data->sum('trs_amount_all_customer')*0.1) }}</span></li>
                    <li class="po_success_total_rmb">DT tỷ giá <i>(30 * Tổng giá tệ)</i> <span class="pull-right">{{ number_format($data->sum('po_success_total_rmb')*30) }}</span></li>
                    <li class="po_success_offer">DT đàm phán <i>(85%)</i> <span class="pull-right">{{ number_format($data->sum('po_success_offer')*0.85) }}</span></li>
                    <li class="total_revenue">
                        Tổng doanh thu
                        @php
                            $total = $data->sum('po_success_service_fee') 
                            + ($data->sum('trs_amount_all_customer')*0.1) 
                            + ($data->sum('po_success_total_rmb')*30) 
                            + ($data->sum('po_success_offer')*0.85);
                        @endphp
                        <span class="pull-right text-red">{{ number_format($total) }}</span>
                    </li>
                </ul>
            </td>
        </tr>
        @foreach ($data as $key => $value)
        <tr>
            <td>{{ $key+1 }}</td>
            <td style="width: 100px">
                <ul data-note="Thông tin nhân viên">
                    <li class="employee_name">{{ $value->employee->name }}</li>
                    <li class="joining_date">{{ date('d/m/Y', strtotime($value->employee->created_at)) }}</li>
                    <li></li>
                    <li><a href="{{ route('admin.sale_salary_details.show', $value->id) }}">Xem chi tiết</a></li>
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
                    <li class="po_success_new_customer">DS KH mới <span class="pull-right">{{ number_format($value->po_success_new_customer) }}</span></li>
                    <li class="po_success_old_customer">DS KH cũ <span class="pull-right">{{ number_format($value->po_success_old_customer) }}</span></li>
                    <li class="po_success_all_customer">Tổng DS <span class="pull-right text-red">{{ number_format($value->po_success_all_customer) }}</span></li>
                    <li class="po_success_service_fee">Phí dịch vụ <span class="pull-right text-red">{{ number_format($value->po_success_service_fee) }}</span></li>
                    <li class="po_success_total_rmb">Tổng tệ <span class="pull-right text-red">{{ number_format($value->po_success_total_rmb) }}</span></li>
                    <li class="po_success_offer">Đàm phán <span class="pull-right text-red">{{ number_format($value->po_success_offer) }}</span></li>
                </ul>
            </td>
            <td>
                <ul data-note="Đơn order chưa hoàn thành">
                    <li class="po_not_success">Số lượng <span class="pull-right">{{ $value->po_not_success }}</span></li>
                    <li class="po_not_success_new_customer">DS KH mới <span class="pull-right">{{ number_format($value->po_not_success_new_customer) }}</span></li>
                    <li class="po_not_success_old_customer">DS KH cũ <span class="pull-right">{{ number_format($value->po_not_success_old_customer) }}</span></li>
                    <li class="po_not_success_all_customer">Tổng DS <span class="pull-right  text-red">{{ number_format($value->po_not_success_all_customer) }}</span></li>
                    <li class="po_not_success_service_fee">Phí dịch vụ <span class="pull-right">{{ number_format($value->po_not_success_service_fee) }}</span></li>
                    <li class="po_not_success_owed">Tổng cọc <span class="pull-right">{{ number_format($value->po_not_success_deposited) }}</span></li>
                    <li class="po_not_success_owed">Công nợ <span class="pull-right  text-red">{{ number_format($value->po_not_success_owed) }}</span></li>
                </ul>
            </td>
            <td>
                <ul data-note="Đơn hàng vận chuyển">
                    <li class="transport_order">Số lượng <span class="pull-right">{{ $value->transport_order }}</span></li>
                    <li class="trs_kg_new_customer">KG KH mới <span class="pull-right">{{ $value->trs_kg_new_customer }}</span></li>
                    <li class="trs_kg_old_customer">KG KH cũ <span class="pull-right">{{ $value->trs_kg_old_customer }}</span></li>
                    <li class="trs_kg_all_customer">Tổng KG <span class="pull-right text-red">{{ $value->trs_kg_all_customer }}</span></li>
                    <li class="trs_m3_new_customer">M3 KH mới <span class="pull-right">{{ $value->trs_m3_new_customer }}</span></li>
                    <li class="trs_m3_old_customer">M3 KH cũ <span class="pull-right">{{ $value->trs_m3_old_customer }}</span></li>
                    <li class="trs_m3_all_customer">Tổng M3 <span class="pull-right text-red">{{ $value->trs_m3_all_customer }}</span></li>
                    <li class="trs_amount_new_customer">DT KH mới <span class="pull-right">{{ number_format($value->trs_amount_new_customer) }}</span></li>
                    <li class="trs_amount_old_customer">DT KH cũ <span class="pull-right">{{ number_format($value->trs_amount_old_customer) }}</span></li>
                    <li class="trs_amount_all_customer">Tổng DT <span class="pull-right text-red">{{ number_format($value->trs_amount_all_customer) }}</span></li>
                </ul>
            </td>
            <td>
                <ul data-note="Tổng kết">
                    {{-- <li class="amount_po">Tổng DS Order <span class="pull-right text-red">{{ number_format($value->po_success_all_customer + $value->po_not_success_all_customer) }}</span></li> --}}
                    <li class="po_success">Số đơn TC <span class="pull-right">{{ $value->po_success }}</span></li>
                    <li class="po_success_service_fee">Phí dịch vụ <i>(100%)</i> <span class="pull-right">{{ number_format($value->po_success_service_fee) }}</span></li>
                    <li class="trs_amount_all_customer">DT vận chuyển <i>(10%)</i><span class="pull-right">{{ number_format($value->trs_amount_all_customer*0.1) }}</span></li>
                    <li class="po_success_total_rmb">DT tỷ giá <i>(30 * Tổng giá tệ)</i> <span class="pull-right">{{ number_format($value->po_success_total_rmb*30) }}</span></li>
                    <li class="po_success_offer">DT đàm phán <i>(85%)</i> <span class="pull-right">{{ number_format($value->po_success_offer*0.85) }}</span></li>
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