<style>
    .box-footer {
        position: absolute;
        top: 0;
        width: 100%;
    }
    td {
        padding: 0px !important;
        height: 35px !important;
    }
    td input {
        height: 35px !important;
        border: none !important;
    }
</style>
<div class="">
    <table class="table table-bordered">
        <thead>
            <th>STT</th>
            <th>Mã vận đơn</th>
            <th>Cân nặng (kg)</th>
            <th>Dài (cm)</th>
            <th>Rộng (cm)</th>
            <th>Cao (cm)</th>
            <th>Ứng kéo (rmb)</th>
            <th>Ghi chú</th>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center; width: 40px;">
                    <input type="text" class="form-control" value="1">
                </td>
                <td>
                    <input type="text" class="form-control transport_code" name="transport_code[]">
                </td>
                <td>
                    <input type="text" class="form-control" value="0">
                </td>
                <td>
                    <input type="text" class="form-control" value="0">
                </td>
                <td>
                    <input type="text" class="form-control" value="0">
                </td>
                <td>
                    <input type="text" class="form-control" value="0">
                </td>
                <td>
                    <input type="text" class="form-control" value="0.0">
                </td>
                <td>
                    <input type="text" class="form-control" value="">
                </td>
            </tr>
        </tbody>
    </table>
</div>

@if (isset($mode) && $mode == "popup" && $callbackObj != null && $callbackObj->count() > 0)
    <div id="callback-china-receive" class="modal" role="dialog">
        <div class="modal-dialog modal-lg">

            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title pull-left">Nhập hàng Trung Quốc thành công <i class="fa fa-check-circle-o" aria-hidden="true" style="color: green;"></i></h4>
                    <br>
                </div>
                <div class="modal-body" style="text-align: left">
                    <p>Danh sách mã vận đơn đã được lưu</p>
                    <table class="table table-bordered">
                        <thead>
                            <th>STT</th>
                            <th>Mã vận đơn</th>
                            <th>Cân nặng (kg)</th>
                            <th>Dài (cm)</th>
                            <th>Rộng (cm)</th>
                            <th>Cao (cm)</th>
                            <th>Ứng kéo (rmb)</th>
                        </thead>
                        <tbody>
                            @foreach ($callbackObj as $index => $row)
                                <tr>
                                    <td style="padding: 5px !important; text-align: center">{{ $index+1 }}</td>
                                    <td style="padding: 5px !important; text-align: left">{{ $row->transport_code }}</td>
                                    <td style="padding: 5px !important; text-align: right">{{ $row->kg }}</td>
                                    <td style="padding: 5px !important; text-align: right">{{ $row->length }}</td>
                                    <td style="padding: 5px !important; text-align: right">{{ $row->width }}</td>
                                    <td style="padding: 5px !important; text-align: right">{{ $row->height }}</td>
                                    <td style="padding: 5px !important; text-align: right">{{ $row->advance_drag }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
        $( document ).ready(function() {
            $("#callback-china-receive").modal('toggle');
            $("#callback-china-receive").on("hidden.bs.modal", function () {
                window.location.href = "/admin/china_receives";
            });
        });
    </script>
@endif

<script>
    $( document ).ready(function() {
        $('.fields-group .col-md-12 label.col-sm-2').remove();
        $('.fields-group .col-md-12 div.col-sm-8').addClass('col-lg-12');
        $('.box-footer .col-md-2').remove();
        $('button[type="reset"]').remove();
        $('button[type="submit"]').html("Lưu dữ liệu");

        $(document).on('keydown','input', function(e) {
            if (e.which == 13) 
            {
                e.preventDefault();
                $(this).parent().parent().next().find('.transport_code').focus();
            }
        });

        $("input").bind("paste", function(e) {
            var iThis = $(this)
            setTimeout(function () {
                iThis.parent().parent().next().find('.transport_code').focus();
            }, 100);
        } );
    });
</script>