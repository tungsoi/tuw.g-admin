<style>
    table , th, td {
        border-color: #d2d6de !important;
    }
    thead, th {
        text-align: center;
    }
</style>
<table class="table table-bordered" id="table-warehouse">
    <thead>
        <th>STT</th>
        <th>Cân nặng</th>
        <th>Dài (cm)</th>
        <th>Rộng (cm)</th>
        <th>Cao (cm)</th>
        <th>Quy cách đóng gói</th>
        <th>Ghi chú</th>
    </thead>
    <tbody>
        <tr class="default-row" style="display: none">
            <td>
                <input type="text" name="order[]" class="form-control order" value="1">
            </td>
            <td>
                <input type="text" name="weight[]" class="form-control">
            </td>
            <td>
                <input type="text" name="lenght[]" class="form-control">
            </td>
            <td>
                <input type="text" name="width[]" class="form-control">
            </td>
            <td>
                <input type="text" name="height[]" class="form-control">
            </td>
            <td>
                <select class="form-control" name="line[]">
                    @foreach ($line as $key => $row)
                        <option value="{{$key}}" @if ($key != 0) selected @endif>{{ $row }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="text" name="note[]" class="form-control">
            </td>
        </tr>
        <tr class="empty-row">
            <td>
                <input type="text" name="order[]" class="form-control" value="1">
            </td>
            <td>
                <input type="text" name="weight[]" class="form-control">
            </td>
            <td>
                <input type="text" name="lenght[]" class="form-control">
            </td>
            <td>
                <input type="text" name="width[]" class="form-control">
            </td>
            <td>
                <input type="text" name="height[]" class="form-control">
            </td>
            <td>
                <select class="form-control" name="line[]">
                    @foreach ($line as $key => $row)
                        <option value="{{$key}}" @if ($key != 0) selected @endif>{{ $row }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="text" name="note[]" class="form-control">
            </td>
        </tr>
    </tbody>
</table>

<button class="btn btn-xs btn-success" id="btn-add-row" type="button">Thêm dòng</button>

<hr>

<button class="btn btn-xs btn-success" id="btn-submit" type="button">Xác nhận</button>

<script>

    let order = $('tr.empty-row').length+1;
    $(document).on('click', '#btn-add-row', function () {
        let default_row = $('.default-row').clone();

        default_row.css('display', 'table-row');
        default_row.removeClass('default-row');
        default_row.find('.order').val(order++);
        $('#table-warehouse tbody').append(default_row);
    });

    $(document).on('keydown', function(e) {
        if (e.which == 13) 
        {
            let default_row = $('.default-row').clone();

            default_row.css('display', 'table-row');
            default_row.removeClass('default-row');
            default_row.find('.order').val(order++);
            $('#table-warehouse tbody').append(default_row);
        }
    });

    $("#btn-submit").on('click', function () {
        $("form").submit();
    });
</script>