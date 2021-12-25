
<button class="btn btn-primary btn-xs" type="button" id="btn-toggle-template">+ Mẫu</button>

<br>
<div id="div-template" style="display: none;">
<h4>Bảng mẫu</h4>
<table class="table table-bordered" id="table-warehouse-template">
    <thead>
        <th>Số dòng</th>
        <th>Cân nặng</th>
        <th>Dài (cm)</th>
        <th>Rộng (cm)</th>
        <th>Cao (cm)</th>
        <th>Quy cách đóng gói</th>
        <th>Ghi chú</th>
    </thead>
    <tbody>
        <tr class="template-row">
            <td>
                <input type="text" name="order-template" class="form-control">
            </td>
            <td>
                <input type="text" name="weight-template" class="form-control">
            </td>
            <td>
                <input type="text" name="lenght-template" class="form-control">
            </td>
            <td>
                <input type="text" name="width-template" class="form-control">
            </td>
            <td>
                <input type="text" name="height-template" class="form-control">
            </td>
            <td>
                <select class="form-control" name="line-template">
                    @foreach ($line as $key => $row)
                        <option value="{{$key}}" @if ($key != 0) selected @endif>{{ $row }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="text" name="note-template" class="form-control">
            </td>
        </tr>
    </tbody>
</table>

<button class="btn btn-warning btn-xs" type="button" id="btn-submit-template">Tạo dòng mẫu</button>

</div>