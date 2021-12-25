<style>
    table , th, td {
        border-color: #d2d6de !important;
    }
    thead, th {
        text-align: center;
    }
</style>
<hr>
<h4>Bảng số liệu</h4>
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
                <input type="text" name="weight[]" class="form-control weight">
            </td>
            <td>
                <input type="text" name="lenght[]" class="form-control lenght">
            </td>
            <td>
                <input type="text" name="width[]" class="form-control width">
            </td>
            <td>
                <input type="text" name="height[]" class="form-control height">
            </td>
            <td>
                <select class="form-control line" name="line[]">
                    @foreach ($line as $key => $row)
                        <option value="{{$key}}" @if ($key != 0) selected @endif>{{ $row }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="text" name="note[]" class="form-control note">
            </td>
        </tr>
        <tr class="empty-row">
            <td>
                <input type="text" name="order[]" class="form-control" value="1">
            </td>
            <td>
                <input type="text" name="weight[]" class="form-control" autofocus>
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
            let last_tr = $('#table-warehouse tbody tr').last();
            console.log(last_tr.find('.weight'));
            last_tr.find('.weight').focus();
        }
    });

    $("#btn-submit").on('click', function () {
        $("form").submit();
    });

    $('#btn-toggle-template').on('click', function () {
        $("#div-template").toggle();
    });

    $("#btn-submit-template").on('click', function () {
        let number_row = $('input[name="order-template"]').val();
        let weight = $('input[name="weight-template"]').val();
        let lenght = $('input[name="lenght-template"]').val();
        let width = $('input[name="width-template"]').val();
        let height = $('input[name="height-template"]').val();
        let line = $('select[name="line-template"]').val();
        let note = $('input[name="note-template"]').val();

        if (number_row > 0) {
            for (let i = 1; i <= number_row; i++) {
                $('#table-warehouse tbody').find('tr.empty-row').remove();

                let default_row = $('.default-row').clone();

                default_row.css('display', 'table-row');
                default_row.removeClass('default-row');

                default_row.find('.order').val(i);
                default_row.find('.weight').val(weight);
                default_row.find('.lenght').val(lenght);
                default_row.find('.width').val(width);
                default_row.find('.height').val(height);
                default_row.find('.line').val(line);
                default_row.find('.note').val(note);

                $('#table-warehouse tbody').append(default_row);
            }
        }
        console.log(number_row, weight, lenght, width, height, line, note);
    });
</script>