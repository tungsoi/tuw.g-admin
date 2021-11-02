{{-- <div class="row">
    <div class="col-md-12">
        <h5>Tiền tạm tính: <b class="estimate-amount" style="color: red">0.00</b> (Tệ) = <b class="estimate-amount-vnd" style="color: red">0</b> (VND)</h5>
    </div>
</div>
<table class="table table-bordered">
    <thead>
        <th>
            <input type="checkbox" class="choose-item-all" id="">
        </th>
        <th>STT</th>
        <th>Ảnh sản phẩm</th>
        <th>Tên sản phẩm</th>
        <th>Link sản phẩm</th>
        <th>Size</th>
        <th>Màu</th>
        <th>Số lượng</th>
        <th>Đơn giá (Tệ)</th>
        <th>Thành tiền (Tệ)</th>
        <th>Ghi chú</th>
        <th>Thao tác</th>
    </thead>
    <tbody>
        @if (sizeof($items) > 0 )
        @foreach ($items as $item)
            @if ($item['items']->count() > 0)
            <tr>
                <td colspan="12" style="background: linen">Tên shop: <b>{{ substr($item['shop_name'], 0, 80) }}...</b></td>
            </tr>

                @foreach ($item['items'] as $key => $item_ele)
                    <tr>
                        <td>
                            <input type="checkbox" class="choose-item" id="" data-index={{ $item_ele->id }}>
                        </td>
                        <td style="width: 50px;">{{ $key+1 }}</td>
                        <td style="width: 100px;">
                            @php
                                if (! $item_ele) {
                                    return null;
                                }
                                else {
                                    $route = "";
                    
                                    if (substr( $item_ele->product_image, 0, 7 ) === "images/") {
                                        $route = asset('storage/admin/'.$item_ele->product_image);
                                    } else {
                                        $route = $item_ele->product_image;
                                    }
                                }
                            @endphp 

                            <img src="{{$route}}" style="width:100px;" class="img img-thumbnail">
                        </td>
                        <td style="width: 300px;">{{ $item_ele->product_name }}</td>
                        <td style="width: 100px;">
                            <a href="{{ $item_ele->product_link }}" target='_blank'>Link sản phẩm</a>
                        </td>
                        <td>
                            {{ $item_ele->product_size }}
                        </td>
                        <td>
                            {{ $item_ele->product_color }}
                        </td>
                        <td style="width: 100px;">
                            {{ $item_ele->qty }}
                        </td>
                        @php
                            $price = $item_ele->price;
                            if (strpos($price, ",") !== false && strpos($price, ".") !== false) {
                                // tồn tại cả dấu , và dấu .
                                $price = str_replace(",", "", $price);
                            } else {
                                if (strpos($price, ",") !== false) {
                                    $price = str_replace(",", ".", $price);
                                }
                            }
                            $price = (float) $price;
                            try {
                                $price = number_format($price, 2, '.', '');
                            } catch (\Exception $e) {
                                $price = 0;
                            }
                        @endphp
                        <td style="width: 100px">
                            {{ $price }}
                        </td>
                        <td style="width: 150px">
                            <span class="item-price" data-index="{{ $item_ele->id }}">{{ str_replace(",", "", number_format($item_ele->qty * $price, 2)) }}</span>
                        </td>
                        <td style="width: 200px;">
                            {{ $item_ele->customer_note }}
                        </td>
                        <td style="width: 80px">

                            <a href="{{ route('admin.carts.edit', $item_ele->id) }}" class="grid-row-edit btn btn-xs btn-warning" data-toggle="tooltip" title="" data-original-title="Chỉnh sửa">
                                <i class="fa fa-edit"></i>
                            </a>

                            <a href="javascript:void(0);" data-url="{{ route('admin.carts.destroy', $item_ele->id) }}" data-id="{{ $item_ele->id }}" class="grid-row-custom-delete btn btn-xs btn-danger" data-toggle="tooltip" title="Xóa">
                                <i class="fa fa-trash"></i>
                            </a>

                        </td>
                    </tr>
                @endforeach
            @endif
        @endforeach
        @else
        <tr>
            <td colspan="12" style="text-align: center;">
                <i>Chưa có sản phẩm</i>
            </td>
        </tr>
        @endif
    </tbody>
</table> --}}
<h5>Tiền tạm tính: <b class="estimate-amount" style="color: red">0.00</b> (Tệ) = <b class="estimate-amount-vnd" style="color: red">0</b> (VND)</h5>
<input type="hidden" name="" class="exchange_rates" value="{{ $exchange_rates }}">

<div id="myModal-storeOrderFromCart" class="modal fade" role="dialog">
    <div class="modal-dialog">
  
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Tạo đơn hàng</h4>
        </div>
        <div class="modal-body">
          <form action="{{ route('admin.customer_purchase_orders.storeFromCart') }}" method="post" id="frm-cart">
                {{ @csrf_field() }}
                <div class="form-group">
                    <select class="form-control" name="warehouse_id">
                        @foreach ($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->code ." (".$warehouse->name." - ".$warehouse->address. ")" }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <input type="radio" id="order_type_1688" name="order_type" value="Taobao-1688" checked>
                    <label for="order_type_1688"> &nbsp; 1688, Taobao</label><br>
                    <input type="radio" id="order_type_pindoudou" name="order_type" value="Pindoudou">
                    <label for="order_type_pindoudou"> &nbsp; Pindoudou</label><br>
                    <input type="radio" id="order_type_wechat" name="order_type" value="Wechat">
                    <label for="order_type_wechat"> &nbsp; Wechat</label><br>
                </div>
                <div class="form-group">
                    <input type="hidden" name="ids" id="ids" value="">
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-success btn-sm">Tạo đơn</button>
                    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Huỷ bỏ</button>
                </div>
          </form>
        </div>
      </div>
  
    </div>
</div>

<script>
    // $('.grid-row-checkbox').iCheck({checkboxClass:'icheckbox_minimal-blue'}).on('ifChanged', function () {

    //     var id = $(this).data('id');

    //     console.log(id, "id");
    //     if (this.checked) {
    //         $.admin.grid.select(id);
    //         $(this).closest('tr').css('background-color', '#ffffd5');
    //     } else {
    //         $.admin.grid.unselect(id);
    //         $(this).closest('tr').css('background-color', '');
    //     }
    //     }).on('ifClicked', function () {

    //     var id = $(this).data('id');

    //     if (this.checked) {
    //         $.admin.grid.unselect(id);
    //     } else {
    //         $.admin.grid.select(id);
    //     }

    //     var selected = $.admin.grid.selected().length;

    //     if (selected > 0) {
    //         $('.grid-select-all-btn').show();
    //     } else {
    //         $('.grid-select-all-btn').hide();
    //     }

    //     $('.grid-select-all-btn .selected').html("{n} sản phẩm được chọn".replace('{n}', selected));
    // });
</script>

{{-- <script>
    let exchange_rates = $('.exchange_rates').val();

    $(document).on('click', '.choose-item-all', function () {

        if ( $(this).is(':checked') ) {
            // tick all

            console.log('oke');

            $('.choose-item:checked').click();
            $('.choose-item').click();
        } else {
            // bo tick all
            $('.choose-item:checked').click();
        }
    });


    $(document).on('click', '.choose-item', function () {

        if ( $(this).is(':checked') ) {
            $(this).parent().parent().css('background', 'wheat');
        } else {
            $(this).parent().parent().css('background', 'white');
        }

        $('.estimate-amount').html(0.00);
        $('.estimate-amount-vnd').html(0);

        let amount = parseFloat($('.estimate-amount').html());

        $(".choose-item:checked").each(function (index, obj) {
            let iIndex = $(obj).data('index');
            let iPrice = $('.item-price[data-index='+iIndex+']').html();

            amount += parseFloat(iPrice);
        })

        amount = parseFloat(amount).toFixed(2);
        let amount_vnd = parseFloat(amount * exchange_rates, 0).toFixed(0);

        $('.estimate-amount').html(amount);
        $('.estimate-amount-vnd').html(amount_vnd);
        $('.estimate-amount-vnd').digits();
    });

    $.fn.digits = function(){ 
        return this.each(function(){ 
            $(this).text( $(this).text().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,") ); 
        })
    }

    $('.grid-row-custom-delete').on('click', function () {

        let url = $(this).data('url');
        let id = $(this).data('id');

        Swal.fire({
            title: 'Bạn có chắc chắn muốn xoá?',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Đồng ý',
            cancelButtonText: 'Huỷ bỏ'
        }).then((result) => {
            if (result.value == true && result.dismiss == undefined) {

                $('.loading-overlay').show();
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax(
                {
                    url: url,
                    type: 'delete', // replaced from put
                    dataType: "JSON",
                    success: function (response)
                    {
                        if (response.isRedirect) {
                            setTimeout(function () {
                                window.location.href = response.url;
                            }, 1000);
                        } else {
                            setTimeout(function () {
                                window.location.reload();
                            }, 1000);
                        }
                        
                    }
                });
            }
        })

    });

    $('.btn-create-order').on('click', function () {
        let check_checked = $(".choose-item:checked");

        if (check_checked.length == 0) {
            $.admin.toastr.error('Vui lòng chọn sản phẩm !', '', {positionClass: 'toast-top-center'});
        } else if (check_checked.length > 30) {
            $.admin.toastr.error('Vui lòng chọn tối đa 30 link sản phẩm !', '', {positionClass: 'toast-top-center'});
        } else {

            let ids = [];
            check_checked.each(function (index, obj) {
                ids.push($(obj).data('index'));
            })

            $("#myModal-storeOrderFromCart #ids").val(ids);
            $("#myModal-storeOrderFromCart").modal('show');

        }
    });
    
    $('#frm-cart').on('submit', function () {
        $('.loading-overlay').show();
    });
</script> --}}