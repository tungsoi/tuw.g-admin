<div class="row">
    <div class="col-md-12">
        <div class="box grid-box" style="padding: 0px 15px;">
            
            <center><h4>{{ $report->title }}</h4></center> <br>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        @foreach ($header as $value)
                            <th>{{ $value }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($body as $key => $item)
                        <tr @php
                            if (isset($color[$key])) {
                                $style = "background: " . $color[$key];
                            } else {
                                $style = "";
                            }
                        @endphp style="{{ $style }}">
                            @foreach ($item as $value)
                                <td>{{ $value }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>