@if (isset($items) && $items->count() > 0)
    @foreach ( $items as $item)

    <tr>
        <td>{{ $item->order->order_number }}</td>
        <td>{{ $item->order->transport_code }}</td>
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
        <td>{{ $item->statusText->name }}</td>
        <td>
            <button class="btn btn-sm btn-warning vn-receive-item" data-pk="{{ $item->id }}">Đã về kho VN</button>
        </td>
    </tr>
        
    @endforeach
@endif

<script>
    
</script>