<h4>Thời điểm cập nhật dữ liệu: {{ date('H:i | d-m-Y', strtotime($data['created_at'])) }}</h4>
<h4>Đơn hàng thanh toán: Đã xuất kho </h4>
<h4>Ngày thanh toán: {{ $report->begin_date . " 00:00:01" }} đến  {{ $report->finish_date . " 23:59:59" }}</h4>
<hr>
<ul class="nav nav-pills">
    <li class="active"><a data-toggle="tab" href="#home">Cân nặng vận chuyển theo từng khách hàng</a></li>
    {{-- <li><a data-toggle="tab" href="#menu1">Khách hàng mới</a></li>
    <li><a data-toggle="tab" href="#menu2">Đơn hàng thành công</a></li>
    <li><a data-toggle="tab" href="#menu3">Đơn hàng thành công theo khách hàng mới</a></li>
    <li><a data-toggle="tab" href="#menu4">Đơn hàng chưa hoàn thành</a></li>
    <li><a data-toggle="tab" href="#menu5">Đơn hàng chưa hoàn thành theo khách hàng mới</a></li>
    <li><a data-toggle="tab" href="#menu6">Đơn hàng vận chuyển</a></li>
    <li><a data-toggle="tab" href="#menu7">Đơn hàng vận chuyển theo khách hàng mới</a></li> --}}
</ul>
  
<div class="tab-content">
    <div id="home" class="tab-pane fade in active">
        <br><br>
        <table class="table table-bordered">
            <tfoot>
                <tr class="totalRow">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>{{ $total }}</td>
                    <td>{{ $m3 }}</td>
                    <td>{{ $count }}</td>
                    <td>{{ number_format($amount) }}</td>
                </tr>
            </tfoot>
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Mã khách hàng</th>
                    <th>Tên khách hàng</th>
                    <th>Số dư ví</th>
                    <th>Ngày tạo tài khoản</th>
                    <th>Cận nặng</th>
                    <th>Khối</th>
                    <th>Số đơn hàng</th>
                    <th>Tổng tiền</th>
                    {{-- <th>Ngày giao dịch gần nhất</th>
                    <th>Ghi chú</th> --}}
                </tr>
            </thead>
            <tbody>
                @foreach ($customers as $key => $customer)
                    <tr>
                        <td>{{ $key+1 }}</td>
                        <td>{{ $customer->symbol_name }}</td>
                        <td>{{ $customer->username }}</td>
                        <td>{{ number_format($customer->wallet) }}</td>
                        <td>{{ date('H:i | d-m-Y', strtotime($customer->created_at)) }}</td>
                        <td>{{ $customer->weight }}</td>
                        <td>{{ $customer->m3 }}</td>
                        <td>{{ $customer->count_order }}</td>
                        <td>{{ number_format($customer->amount) }}</td>
                        {{-- <td>
                            <p>Tạo đơn order: 
                                {{$customer->last_action['purchase_created'] != null ? date('H:i | d-m-Y', strtotime($customer->last_action['purchase_created'])) : null }}</p>
                            <p>Cọc đơn order: {{$customer->last_action['purchase_deposited'] != null ? date('H:i | d-m-Y', strtotime($customer->last_action['purchase_deposited'])) : null }}</p>
                            <p>Tạo đơn vận chuyển: {{$customer->last_action['transport_created'] != null ? date('H:i | d-m-Y', strtotime($customer->last_action['transport_created'])) : null }}</p>
                        </td>
                        <td>
                            @php
                                $note = "";
                                if ($customer->last_action['purchase_created'] != null)
                                {
                                    $time = strtotime($customer->last_action['purchase_created']);
                                    $now = strtotime(now());
                                    $int = 7 * 24 * 60 * 60 * 1000;

                                    if ($now - $time >= $int) {
                                        $note .= "- Chưa phát sinh thêm đơn hàng order \n";
                                    }
                                }

                                if ($customer->last_action['transport_created'] != null)
                                {
                                    $time = strtotime($customer->last_action['transport_created']);
                                    $now = strtotime(now());
                                    $int = 7 * 24 * 60 * 60 * 1000;

                                    if ($now - $time >= $int) {
                                        $note .= "- Chưa phát sinh thêm đơn hàng vận chuyển \n";
                                    }
                                }
                            @endphp

                            <p style="color: red">{{ $note }}</p>
                        </td> --}}
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    {{-- <div id="menu1" class="tab-pane fade">
        <br><br>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Mã khách hàng</th>
                    <th>Tên khách hàng</th>
                    <th>Số dư ví</th>
                    <th>Ngày tạo tài khoản</th>
                </tr>
            </thead>
            <tbody>
                @if ($data['log_new_customer'] != null)
                    @php
                        $customers = json_decode($data['log_new_customer'])
                    @endphp
        
                    @if ($customers != null)
                        @foreach ($customers as $key => $customer)
                            @php
                                $customer_data = \App\User::find($customer->id);
                            @endphp

                            <tr>
                                <td>{{ $key+1 }}</td>
                                <td>{{ $customer_data->symbol_name }}</td>
                                <td>{{ $customer_data->name }}</td>
                                <td>{{ number_format($customer_data->wallet) }}</td>
                                <td>{{ date('d-m-Y', strtotime($customer_data->created_at)) }}</td>
                            </tr>
                        @endforeach
                    @endif
                @endif
            </tbody>
        </table>
    </div>
    <div id="menu2" class="tab-pane fade">
        <br><br>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>STT</th>
                    <th>Mã đơn hàng</th>
                    <th>Mã khách hàng</th>
                    <th>Trạng thái</th>
                    <th>Tổng giá trị SP (Tệ)</th>
                    <th>Phí dịch vụ SP (Tệ)</th>
                    <th>Tổng phí vận chuyển nội địa (Tệ)</th>
                    <th>Đã cọc (VND)</th>
                    <th>Tổng giá cuối (VND)</th>
                </tr>
            </thead>
            <tbody>
                @if ($data['log_success_order'] != null)
                    @php
                        $orders = json_decode($data['log_success_order'])
                    @endphp
        
                    @if ($orders != null)
                        @foreach ($orders as $key => $order)
                            @php
                                $row = \App\Models\Aloorder\PurchaseOrder::find($order->id);
                            @endphp

                            <tr>
                                <td>{{ $key+1 }}</td>
                                <td>{{ $row->order_number }}</td>
                                <td>{{ $row->customer->symbol_name }}</td>
                                <td>{{ \App\Models\Aloorder\PurchaseOrder::STATUS[$row->status] }}</td>
                                <td>{{ $row->sumQtyRealityMoney() }}</td>
                            </tr>
                        @endforeach
                    @endif
                @endif
            </tbody>
        </table>
    </div> --}}
</div>