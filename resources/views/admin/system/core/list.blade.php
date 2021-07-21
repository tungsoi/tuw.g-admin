<style>
    a:hover {
        color: #55acee !important;
    }
</style>
<ul style="padding-left: 15px;">
    @foreach ($data as $key => $row)
        <li style="margin: 3px 0px;">
            @if (isset($row['is_link']) && $row['is_link'])
                <a href="{{ $row['route'] }}" target="_blank" style="color: black">{!! $row['text'] !!}</a>
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