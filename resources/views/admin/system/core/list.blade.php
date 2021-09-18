{{-- <style>
    a:hover {
        color: #55acee !important;
    }
</style> --}}
<ul style="padding-left: 0px; list-style: none;">
    @foreach ($data as $key => $row)
        <li style="margin: 3px 0px;">
            @if (isset($row['is_link']) && $row['is_link'])
                <a href="{{ $row['route'] }}" target="_blank" @if (isset($row['style'])) style="{{ $row['style'] }}; color: #3c8dbc;" @endif>{!! $row['text'] !!}</a>
            @elseif (isset($row['is_image']) && $row['is_image'])
                <img src="{{ $row['link'] }}" style="max-width:70px;max-height:70px" class="img img-thumbnail">
            @else
                @if ($row['is_label'])
                        <span class="label label-{{ $row['color'] }}">{!! $row['text'] !!}</span>
                @else
                    {!! $row['text'] !!}
                @endif
            @endif
        </li>
    @endforeach
</ul>