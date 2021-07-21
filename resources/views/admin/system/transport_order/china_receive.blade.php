<style>
    .box-footer {
        position: absolute;
        top: 0;
        width: 100%;
        display: none;
    }
    td {
        padding: 0px !important;
        height: 35px !important;
    }
    td input {
        height: 35px !important;
        border: none !important;
        width: 100%;
    }
</style>
<div class="">
    <div class="row" style="display: none" id="alert-box">
        <div class="col-md-12">
            <div class="" id="alert-content">
                <p id="alert-loading"><i class="fa fa-spinner fa-spin"></i> Đang kiểm tra ...</p>
                <p id="alert-text"></p>
            </div>
        </div>
    </div>
    <table class="table table-bordered" id="tbl-china-receive">
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
            @for ($i = 1; $i <= 20; $i++)
                @php
                    $disabled = ($i == 1) ? false : true;
                @endphp
                <tr>
                    <td style="text-align: center; width: 40px;">
                        <input type="text" class="form-control order_number" readonly value="{{ $i }}" name="order_number">
                    </td>
                    <td style="width: 300px">
                        <input type="text" class="form-control transport_code" name="transport_code[]" 
                            @if ($disabled) disabled @endif>
                    </td>
                    <td>
                        <input type="text" class="form-control" readonly value="0">
                    </td>
                    <td>
                        <input type="text" class="form-control" readonly value="0">
                    </td>
                    <td>
                        <input type="text" class="form-control" readonly value="0">
                    </td>
                    <td>
                        <input type="text" class="form-control" readonly value="0">
                    </td>
                    <td>
                        <input type="text" class="form-control" readonly value="0.0">
                    </td>
                </tr>
            @endfor
        </tbody>
    </table>
</div>

<script>
    const CHECK_TRANSPORT_CODE_URL = "transport_codes/seach";
    const STORE_TRANSPORT_CODE_URL = "china_receives/storeChinaReceive";
    const ALERT_STATUS_DANGER = 'alert alert-danger';
    const ALERT_STATUS_SUCCESS = 'alert alert-success';
    const ALERT_BOX = $('#alert-box');
    const ALERT_CONTENT = $('#alert-content');
    const ALERT_TEXT = $('#alert-text');
    const ALERT_LOADING = $('#alert-loading');

    $( document ).ready(function() {
        // init
        $('label.col-sm-2').remove();
        $('div.col-sm-8').addClass('col-lg-12');
        $('.box-footer .col-md-2').remove();
        $('button[type="reset"]').remove();
        $('button[type="submit"]').html("Lưu dữ liệu");
        // end

        // event paste
        $('.transport_code').bind("paste", function(e) {
            var iThis = $(this)
            var transportCode = e.originalEvent.clipboardData.getData('text');

            if (transportCode != "") {
                initAlertBox();
                showAlertLoading();
                checkTransportCode(transportCode, iThis);
            }
        } );

        // event enter input, then click enter button
        $(document).on('keydown','input', function(e) {
            initAlertBox();
            if (e.which == 13) 
            {
                e.preventDefault();
                iThis = $(this)
                var transportCode = iThis.val();

                if (transportCode != "") {
                    showAlertLoading();
                    checkTransportCode(transportCode, iThis);
                }

                
            }
        });

        // check transport_code
        function checkTransportCode(code, iThis) {
            $.ajax({
                url: CHECK_TRANSPORT_CODE_URL + "/" + code,
                type: 'GET',
                dataType: "JSON",
                success: function (response)
                {
                    let status = "";
                    let text = "";

                    // if transport code is exists
                    if (response.data != null) {
                        status = ALERT_STATUS_DANGER;
                        text = "Mã vận đơn "+ code +" đã tồn tại.";

                        showAlertDanger(status, text);
                    } else {
                        saveTransportCode(code, iThis);
                    }
                }
            });
        }

        // show alert and set status, set text
        function initAlertBox() {
            ALERT_TEXT.html("");
            ALERT_LOADING.show();
            ALERT_CONTENT.removeClass();
            ALERT_BOX.hide();
        }
        function showAlertDanger(status, text) {
            ALERT_CONTENT.removeClass();
            ALERT_CONTENT.addClass(status);
            ALERT_TEXT.html(text);
            ALERT_LOADING.hide();
        }

        function showAlertLoading() {
            ALERT_BOX.show();
        }
        // end

        // save transport code
        function saveTransportCode(code, iThis) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            $.ajax({
                url: STORE_TRANSPORT_CODE_URL,
                type: 'POST',
                dataType: "JSON",
                data: {
                    'transport_code': code
                },
                success: function (response)
                {
                    if (response) {
                        status = ALERT_STATUS_SUCCESS;
                        text = "Mã vận đơn "+ code +" đã được lưu.";
                        showAlertDanger(status, text);

                        setTimeout(function () {
                            nextRowElement(iThis);
                        }, 500);

                        setTimeout(function () {
                            initAlertBox();
                        }, 4000);
                    }
                }
            });

        }
        // end

        function nextRowElement(iThis) {
            console.log(iThis);
            iThis.parent().parent().next().children().find('.transport_code').prop("disabled", false);
            iThis.parent().parent().next().children().find('.transport_code').focus();
        }
        
    });
</script>