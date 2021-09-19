<div class="box box-info">
    <div class="box-header with-border">
        <h4 class="box-title uppercase">Đặt mua sản phẩm</h4>
        <div class="box-tools">
            <div class="btn-group pull-right" style="margin-right: 5px">
                <a href="{{ route('admin.carts.index') }}" class="btn btn-sm btn-default" title="Danh sách"><i
                        class="fa fa-list"></i><span class="hidden-xs">&nbsp;Danh sách</span></a>
            </div>
        </div>
    </div>
    <!-- /.box-header -->
    <!-- form start -->
    <form action="{{ route('admin.carts.storeAdd1688') }}" method="POST" class="" accept-charset="UTF-8"
        enctype="multipart/form-data">
        {{ csrf_field() }}
        <div class="box-body">
            <div class="fields-group">
                <div class="col-md-12" style="padding: 0px;">
                    <h5>Tổng số sản phẩm: {{ $items->count() }}</h5>
                    <h5 style="color: red"><i>Vui lòng kiểm tra chính xác thông tin sản phẩm. Click "Thực hiện" để lưu vào giỏ hàng.</i></h5>
                </div>
                <div class="col-md-12" style="padding: 0px;">
                    <table class="table table-bordered">
                        <thead>
                            <th>STT</th>
                            <th>Ảnh</th>
                            <th>Tên shop</th>
                            <th>Tên sản phẩm <span style="color: red">(*)</span></th>
                            <th>Link sản phẩm <span style="color: red">(*)</span></th>
                            <th>Kích thước <span style="color: red">(*)</span></th>
                            <th>Màu sắc <span style="color: red">(*)</span></th>
                            <th>Số lượng <span style="color: red">(*)</span></th>
                            <th>Giá (Tệ) <span style="color: red">(*)</span></th>
                            <th>Ghi chú</th>
                        </thead>
                        @if ($items->count() > 0)
                        @foreach ($items as $key => $item)
                        <tr>
                            <td>{{ $key+1 }}</td>
                            <td>
                                <img src="{{ $item->product_image }}" alt="Không có ảnh sản phẩm"
                                    style="width: 70px; border: 1px solid gray; min-height: 70px;">
                            </td>
                            <td>
                                <input value="{{ $item->shop_name != "" ? $item->shop_name : "Không tên" }}" type="text"
                                    name="shop_name[{{ $item->id }}]" value="" class="form-control shop_name"
                                    placeholder="Nhập vào Tên shop" readonly>
                            </td>
                            <td>
                                <input oninvalid="this.setCustomValidity('Vui lòng nhập nội dung')"
                                    oninput="setCustomValidity('')" required value="{{ $item->product_name }}"
                                    type="text" name="product_name[{{ $item->id }}]" value=""
                                    class="form-control product_name" placeholder="Nhập vào Tên sản phẩm">
                            </td>
                            <td>
                                <input oninvalid="this.setCustomValidity('Vui lòng nhập nội dung')"
                                    oninput="setCustomValidity('')" required value="{{ $item->product_link }}"
                                    type="text" name="product_link[{{ $item->id }}]" value=""
                                    class="form-control product_link" placeholder="Nhập vào Link sản phẩm">
                            </td>
                            <td>
                                <input oninvalid="this.setCustomValidity('Vui lòng nhập nội dung')"
                                    oninput="setCustomValidity('')" required value="{{ $item->product_size != "" ? $item->product_size : $item->product_color }}"
                                    type="text" name="product_size[{{ $item->id }}]" value=""
                                    class="form-control product_size" placeholder="Nhập vào Size sản phẩm">
                            </td>
                            <td>
                                <input oninvalid="this.setCustomValidity('Vui lòng nhập nội dung')"
                                    oninput="setCustomValidity('')" required value="{{ $item->product_color != "" ? $item->product_color : $item->product_size }}"
                                    type="text" name="product_color[{{ $item->id }}]" value=""
                                    class="form-control product_color" placeholder="Nhập vào Màu sắc sản phẩm">
                            </td>
                            <td>
                                <input oninvalid="this.setCustomValidity('Vui lòng nhập nội dung')"
                                    oninput="setCustomValidity('')" required value="{{ $item->qty }}" type="text"
                                    name="qty[{{ $item->id }}]" value="" class="form-control qty"
                                    placeholder="Nhập vào Size">
                            </td>
                            <td>
                                @php
                                    try {
                                        $price = (float) str_replace(",", ".", $item->price);
                                        $price = number_format($price, 2, '.', '');
                                    } catch (\Exception $e) {
                                        $price = "";
                                    }
                                @endphp
                                <input oninvalid="this.setCustomValidity('Vui lòng nhập nội dung')"
                                    oninput="setCustomValidity('')" required value="{{ $price }}"
                                    style="width: 120px; text-align: right;" type="text" name="price[{{ $item->id }}]"
                                    value="" class="form-control price" placeholder="Nhập vào Giá sản phẩm (Tệ)">
                            </td>
                            <td>
                                <input value="{{ $item->customer_note }}" name="customer_note[{{ $item->id }}]"
                                    class="form-control customer_note" rows="5" placeholder="Nhập vào Ghi chú của bạn">
                            </td>
                            <input type="hidden" name="id[{{ $item->id }}]">
                        </tr>
                        @endforeach
                        @else
                        <tr>
                            <td colspan="9">Không có sản phẩm nào</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
        <!-- /.box-body -->
        <div class="box-footer">
            <div class="col-md-8">
                <div class="btn-group pull-left">
                    <button type="submit" class="btn btn-success btn-sm">Thực hiện</button>
                </div> &nbsp;  &nbsp;
                <div class="btn-group pull-left" style="margin-left: 20px;">
                    <button type="reset" class="btn btn-warning btn-sm">Đặt lại</button>
                </div>
            </div>
        </div>
    </form>
</div>