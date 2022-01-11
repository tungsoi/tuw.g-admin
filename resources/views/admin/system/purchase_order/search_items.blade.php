@if (isset($items) && $items->count() > 0)
    @foreach ( $items as $item)

    <tr>
        <td>
            @php
                $link = $item->order ? 
                        route('admin.purchase_orders.show', $item->order->id)
                        : '#';
            @endphp
 
            <a href="{{$link}}" target="_blank">{{ $item->order ? $item->order->order_number : "" }}</a> <br>
            <span>{{ $item->order ? $item->order->customer->symbol_name : "" }}</span>
        </td>
        <td style="width: 200px !important;">{{ $item->cn_code }}</td>
        <td>
            @php
                if (strpos($item->product_image, 'images/') === false) {
                    $link = $item->product_image;
                } else {
                    $link = asset("storage/admin/". $item->product_image);
                }
            @endphp

            <a href="{{ $link }}" class="grid-popup-link">
                <img src="{{ $link }}" style="max-width:40px;max-height:200px" class="img img-thumbnail">
            </a>
        </td>
        <td>
            <a href="{{ $item->product_link }}">Link</a>
        </td>
        <td>{{ $item->product_size }}</td>
        <td>{{ $item->product_color }}</td>
        <td>{{ $item->price }}</td>
        <td>{{ $item->purchase_cn_transport_fee }}</td>
        <td>---</td>
        <td>
            <span class="status" @if($item->status == 3) style='color: green; font-weight: bold;' @endif>{{ $item->statusText->name }}</span>
        </td>
        <td>
            @if ($item->status != 3 && $item->status != 4)
                <button class="btn btn-sm btn-warning vn-receive-item" data-pk="{{ $item->id }}">Đã về kho VN</button>
            @endif
        </td>
    </tr>
        
    @endforeach
@endif

<script>
    $(document).on('click', '.vn-receive-item', function () {
        let iThis = $(this);
        $.ajax({
            url: '/admin/vn_received',
            type: 'POST',
            dataType: "JSON",
            data: {
                id: $(this).data('pk')
            },
            success: function (response)
            {
                if (response.status) {
                    $.admin.toastr.success("Đã tích nhận", '', {timeOut: 2000, preventDuplicates: true});
                    iThis.parent().prev().find('.status').html(response.msg);
                    iThis.remove();
                }
            }
        });
    });

    $('.grid-popup-link').magnificPopup({"type":"image"});
</script>