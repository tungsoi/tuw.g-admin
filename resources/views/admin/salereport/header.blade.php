<table class="table">
    <thead>
        <tr>
            <th style="width: 33.33%">KHOẢNG THỜI GIAN</th>
            <th style="width: 33.33%">NGÀY TẠO</th>
            <th style="width: 33.33%">CẬP NHẬT LẦN CUỐI</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ date('d-m-Y', strtotime($data['begin_date']))." đến ".date('d-m-Y', strtotime($data['finish_date'])) }}</td>
            <td>{{ date('H:i | d-m-Y', strtotime($data['created_at'])) }}</td>
            <td>{{ date('H:i | d-m-Y', strtotime($data['updated_at'])) }}</td>
        </tr>
    </tbody>
</table>

<style>
    table i {
        font-size: 10px !important;
        font-weight: 400 !important;
    }
</style>
<div style="overflow: scroll">
<table class="table table-bordered" style="min-width: 2000px !important; text-align: right">
    <thead style="text-transform: uppercase; text-align: center !important; font-size: 10px;">
        <tr>
            <th rowspan="2" style="text-align: center; background: whitesmoke;">STT</th>
            <th rowspan="2" style="text-align: center; background: whitesmoke;">Họ và tên</th>
            <th colspan="4" style="text-align: center; background: #BDCC94;">KH</th>
            <th colspan="4" style="text-align: center; background: #FFEC67;">đơn hoàn thành</th>
            <th colspan="5" style="text-align: center; background: #A9B6CC;">đơn chưa hoàn thành</th>
            <th rowspan="2" style="text-align: center; background: wheat;">phí dịch vụ <br> <i>(14) = (8) + (13)</i></th>
            <th colspan="5" style="text-align: center; background: #95998A;">vận chuyển</th>
            <th rowspan="2" style="text-align: center; background: green; color: white">Tổng doanh số tháng <br> (19) = (7) + (11)</th>
            {{-- <th rowspan="2" style="text-align: center; background: whitesmoke">Thao tác</th> --}}
        </tr>
        <tr>
            <th style="text-align: center;background: whitesmoke">Cũ <br> <i>(1)</i></th>
            <th style="text-align: center;background: whitesmoke">Mới <br> <i>(2)</i></th>
            <th style="text-align: center;background: whitesmoke">Tổng <br> <i>(3) = (1) + (2) </i></th>
            <th style="text-align: center;background: rgb(182, 47, 47); color: white">Tổng âm ví <br> <i>(4)</i></th>
            <th style="text-align: center;background: whitesmoke">SL <br> <i>(5)</i></th>
            <th style="text-align: center;background: whitesmoke">Doanh số KHM <br> <i>(6)</i></th>
            <th style="text-align: center;background: whitesmoke">Tổng doanh số <br> <i>(7)</i></th>
            <th style="text-align: center;background: whitesmoke">Phí dịch vụ <br> <i>(8)</i></th>
            <th style="text-align: center;background: whitesmoke">SL <br> <i>(9)</i></th>
            <th style="text-align: center;background: whitesmoke">Doanh số KHM <br> <i>(10)</i></th>
            <th style="text-align: center;background: whitesmoke">Tổng doanh số <br> <i>(11)</i></th>
            <th style="text-align: center;background: rgb(182, 47, 47); color: white">Công nợ trên đơn <br> <i>(12)</i></th>
            <th style="text-align: center;background: whitesmoke">Phí dịch vụ <br> <i>(13)</i></th>
            <th style="text-align: center;background: whitesmoke">tổng kg <br> <i>(15)</i></th>
            <th style="text-align: center;background: whitesmoke">tổng m3 <br> <i>(16)</i></th>
            <th style="text-align: center;background: whitesmoke">tổng kg KHM <br> <i>(17)</i></th>
            <th style="text-align: center;background: whitesmoke">Doanh thu KHM<br> <i>(18)</i></th>
            <th style="text-align: center;background: whitesmoke">Tổng doanh thu <br> <i>(19)</i></th>
        </tr>
    </thead>
    <tbody>
        @php
            $old_customer = 0;
            $new_customer = 0;
            $total_customer = 0;
            $total_wallet = 0;
            $success_order = 0;
            $success_order_payment_new_customer = 0;
            $success_order_payment = 0;
            $success_order_service_fee = 0;
            $processing_order = 0;
            $processing_order_payment_new_customer = 0;
            $processing_order_payment = 0;
            $owed_processing_order_payment = 0;
            $processing_order_service_fee = 0;
            $total_transport_weight = 0;
            $total_transport_weight_new_customer = 0;
            $total_transport_fee_new_customer = 0;
            $total_transport_fee = 0;
            $total_transport_m3 = 0;
        @endphp
       
        @foreach ($detail as $key => $row)
            @php
                $old_customer += $row->total_customer - $row->new_customer ;
                $new_customer += $row->new_customer;
                $total_customer += $row->total_customer;
                $total_wallet += $row->total_customer_wallet;
                $success_order += $row->success_order;
                $success_order_payment_new_customer += $row->success_order_payment_new_customer;
                $success_order_payment += $row->success_order_payment;
                $success_order_service_fee += $row->success_order_service_fee;
                $processing_order += $row->processing_order;
                $processing_order_payment_new_customer += $row->processing_order_payment_new_customer;
                $processing_order_payment += $row->processing_order_payment;
                $owed_processing_order_payment += $row->owed_processing_order_payment;
                $processing_order_service_fee += $row->processing_order_service_fee;
                $total_transport_weight += $row->total_transport_weight;
                $total_transport_m3 += $row->total_transport_m3;
                $total_transport_weight_new_customer += $row->total_transport_weight_new_customer;
                $total_transport_fee_new_customer += $row->total_transport_fee_new_customer;
                $total_transport_fee += $row->total_transport_fee;
            @endphp
            <tr>
                <td>{{ $key+1 }}</td>
                <td><b style="text-transform: uppercase; font-size: 10px">{{ $row->user->name }}</b> <br> {{ date('Y-m-d', strtotime($row->user->created_at)) }}</td>
                <td>
                    @php
                        if ($portal == "false")
                            $route = "https://alilogi.vn/admin/customers?&name=&symbol_name=&wallet=2&staff_sale_id=" . $row->user_id . "&wallet_sort=&email=&phone_number=&ware_house_id=&province=&district=";
                        else {
                            $route = "#";
                        }
                    @endphp
                    @if ($portal == "false")
                        <a href="{{ $route }}" target="_blank">
                            {{ $row->total_customer - $row->new_customer }}
                        </a> 
                    @else
                        {{ $row->total_customer - $row->new_customer }}
                    @endif
                </td>
                <td>
                    @php
                        if ($portal == "false")
                            $route = "https://alilogi.vn/admin/customers?&name=&symbol_name=&wallet=2&staff_sale_id=" . $row->user_id . "&wallet_sort=&email=&phone_number=&ware_house_id=&province=&district=&created_at%5Bstart%5D=" . $data['begin_date'] . "&created_at%5Bend%5D=" . $data['finish_date'];
                        else {
                            $route = "#";
                        }
                    @endphp

                    @if ($portal == "false")
                        <a href="{{ $route }}" target="_blank">
                            {{ $row->new_customer }}
                        </a>
                    @else
                        {{ $row->new_customer }}
                    @endif
                </td>
                <td>{{ $row->total_customer }}</td>
                <td>{{ number_format($row->total_customer_wallet) }}</td>
                <td>{{ $row->success_order }}</td>
                <td>{{ number_format($row->success_order_payment_new_customer) }}</td>
                <td>{{ number_format($row->success_order_payment) }}</td>
                <td>{{ number_format($row->success_order_service_fee) }}</td>
                <td>{{ $row->processing_order }}</td>
                <td>{{ number_format($row->processing_order_payment_new_customer) }}</td>
                <td>{{ number_format($row->processing_order_payment) }}</td>
                <td>{{ number_format($row->owed_processing_order_payment) }}</td>
                <td>{{ number_format($row->processing_order_service_fee) }}</td>
                <td>{{ number_format($row->processing_order_service_fee + $row->success_order_service_fee) }}</td>
                <td>
                    @php
                        if ($portal == "false")
                            $route = route('admin.revenue_report_fetchs.index')."?id=".$row->id;
                        else {
                            $route = "#";
                        }
                    @endphp
                    @if ($portal == "false")
                    <a href="{{ $route }}" target="_blank">
                        {{ number_format($row->total_transport_weight, 2) }}
                    </a>
                    @else
                        {{ number_format($row->total_transport_weight, 2) }}
                    @endif
                </td>
                <td>{{ number_format($row->total_transport_m3, 3) }}</td>
                <td>
                    @php
                        if ($portal == "false")
                            $route = route('admin.revenue_report_fetchs.index')."?id=".$row->id."&type=new_customer";
                        else {
                            $route = "#";
                        }
                    @endphp

                    @if ($portal == "false")
                        <a href="{{ $route }}" target="_blank">
                            {{ number_format($row->total_transport_weight_new_customer, 2) }}
                        </a>   
                    @else
                        {{ number_format($row->total_transport_weight_new_customer, 2) }}
                    @endif
                </td>
                <td>{{ number_format($row->total_transport_fee_new_customer) }}</td>
                <td>{{ number_format($row->total_transport_fee) }}</td>
                <td>{{ number_format($row->success_order_payment + $row->processing_order_payment) }}</td>
                {{-- <td>
                    @php
                        $route = route('admin.fetchs.index')."?id=".$row->id;
                    @endphp
                    <a class="btn btn-warning btn-sm" href="{{ $route }}">Xem chi tiết</a>
                </td> --}}
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="totalRow">
            <td></td>
            <td></td>
            <td>{{ $old_customer }}</td>
            <td>{{ $new_customer }}</td>
            <td>{{ $total_customer }}</td>
            <td>{{ number_format($total_wallet) }}</td>
            <td>{{ $success_order }}</td>
            <td>{{ number_format($success_order_payment_new_customer) }}</td>
            <td>{{ number_format($success_order_payment) }}</td>
            <td>{{ number_format($success_order_service_fee) }}</td>
            <td>{{ number_format($processing_order) }}</td>
            <td>{{ number_format($processing_order_payment_new_customer) }}</td>
            <td>{{ number_format($processing_order_payment) }}</td>
            <td>{{ number_format($owed_processing_order_payment) }}</td>
            <td>{{ number_format($processing_order_service_fee) }}</td>
            <td>{{ number_format($success_order_service_fee + $processing_order_service_fee) }}</td>
            <td>{{ number_format($total_transport_weight, 2) }}</td>
            <td>{{ number_format($total_transport_m3, 3) }}</td>
            <td>{{ number_format($total_transport_weight_new_customer, 2) }}</td>
            <td>{{ number_format($total_transport_fee_new_customer) }}</td>
            <td>{{ number_format($total_transport_fee) }}</td>
            <td>{{ number_format($success_order_payment + $processing_order_payment) }}</td>
            {{-- <td></td> --}}
        </tr>
    </tfoot>
</table>
</div>