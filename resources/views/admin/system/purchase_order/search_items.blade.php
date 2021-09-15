@if (isset($items) && $items->count() > 0)
    @foreach ( $items as $item)

    <tr>
        <td>{{ $item->order->order_number }}</td>
        <td style="width: 200px !important;">{{ $item->order->transport_code }}</td>
        <td>
            @php
                if (strpos($item->product_image, 'https://') !== false) {
                    $link = $item->product_image;
                } else {
                    $link = asset("storage/admin/". $item->product_image);
                }
            @endphp

            <img src="{{ $link }}" alt="" width="50">
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
            <span class="status">{{ $item->statusText->name }}</span>
        </td>
        <td>
            <button class="btn btn-sm btn-warning vn-receive-item" data-pk="{{ $item->id }}">Đã về kho VN</button>
        </td>
    </tr>
        
    @endforeach
@endif

<script>
    $(document).on('click', '.vn-receive-item', function () {
        let iThis = $(this);
        // console.log('oke');
        $.ajax({
            url: '/admin/vn_received',
            type: 'POST',
            dataType: "JSON",
            data: {
                id: $(this).data('pk')
            },
            success: function (response)
            {
                console.log(response);
                if (response.status) {
                    $.admin.toastr.success("Đã tích nhận", '', {timeOut: 2000});
                    iThis.parent().prev().find('.status').html(response.status);
                    iThis.remove();
                }
            }
        });
    });
</script>