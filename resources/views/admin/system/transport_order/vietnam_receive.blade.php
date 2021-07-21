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
    .col-sm-2 {
        width: 30%;
    }
    .mg-t-5 {
        margin-top: 5px;
    }

    .box-red {
        background: #dd4b39 !important;
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
            <th>Thao tác</th>
        </thead>
        <tbody>
            @for ($i = 1; $i <= 20; $i++)
                @php
                    $disabled = true;
                @endphp
                <tr data-index="{{ $i }}">
                    <td style="text-align: center; width: 40px;">
                        <input type="text" class="form-control" value="{{ $i }}" disabled>
                    </td>
                    <td>
                        <input type="text" class="form-control transport_code" name="transport_code[]" @if ($disabled) disabled @endif>
                    </td>
                    <td>
                        <input type="text" class="form-control kg" name="kg[]" value="" @if ($disabled) disabled @endif>
                    </td>
                    <td>
                        <input type="text" class="form-control length" name="length[]" value="" @if ($disabled) disabled @endif>
                    </td>
                    <td>
                        <input type="text" class="form-control width" name="width[]" value="" @if ($disabled) disabled @endif>
                    </td>
                    <td>
                        <input type="text" class="form-control height" name="height[]" value="" @if ($disabled) disabled @endif>
                    </td>
                    <td>
                        <input type="text" class="form-control advance_drag" name="advance_drag[]" value="" @if ($disabled) disabled @endif>
                    </td>
                    <td>
                        <input type="text" class="form-control note" name="note[]" value="" @if ($disabled) disabled @endif>
                    </td>
                    <td style="text-align: center">
                        <button type="button" class="btn btn-danger btn-sm mg-t-5 btn-delete-code" 
                            @if ($disabled) disabled @endif data-indexrow="{{ $i }}">
                                <i class="fa fa-trash"></i> Xoá
                        </button>
                    </td>
                </tr>
            @endfor
        </tbody>
    </table>
</div>

<script src="{{ asset('vendor/laravel-admin/laravel-admin/autonumeric.min.js') }}"></script>

<script>
    const CHECK_TRANSPORT_CODE_URL = "transport_codes/seach";
    const STORE_TRANSPORT_CODE_URL = "china_receives/storeChinaReceive";
    const ALERT_STATUS_DANGER = 'alert alert-danger';
    const ALERT_STATUS_SUCCESS = 'alert alert-success';
    const ALERT_BOX = $('#alert-box');
    const ALERT_CONTENT = $('#alert-content');
    const ALERT_TEXT = $('#alert-text');
    const ALERT_LOADING = $('#alert-loading');

    function init() {
        $('.fields-group .col-md-12 label.col-sm-2').remove();
        $('.fields-group .col-md-12 div.col-sm-8').addClass('col-lg-12');
        $('.box-footer .col-md-2').remove();
        $('button[type="reset"]').remove();
        $('button[type="submit"]').html("Lưu dữ liệu");

        new AutoNumeric.multiple('.kg', {
            decimalPlaces: 1
        });
        new AutoNumeric.multiple('.length', {
            decimalPlaces: 0
        });
        new AutoNumeric.multiple('.width', {
            decimalPlaces: 0
        });
        new AutoNumeric.multiple('.height', {
            decimalPlaces: 0
        });
        new AutoNumeric.multiple('.advance_drag', {
            decimalPlaces: 2
        });
    }

    $( document ).ready(function() {
        init();

        // check input customer name
        $(document).on('keydown','#customer_code_input', function(e) {
            let customer_code = $(this).val()
            if (e.which == 13 && customer_code != "") 
            {
                e.preventDefault();
                unDisabledRow(1);
            }
        });

        function unDisabledRow(index) {
            $('tr[data-index="'+ index +'"] td input').prop("disabled", false);
            $('tr[data-index="'+ index +'"] td .transport_code').focus();
            $('tr[data-index="'+ index +'"] td button').prop("disabled", false);
        }

        $(document).on('keydown','input', function(e) {
            if (e.which == 13) 
            {
                e.preventDefault();
            }
        });

        $(document).on('keydown','.transport_code', function(e) {
            let iThis = $(this);
            var transportCode = iThis.val();
            removeAlertInputTransportCode();
            checkPastedTransportCode(transportCode, iThis.parent().parent().data('index'));
            if (e.which == 13) 
            {
                e.preventDefault();

                action(iThis, transportCode);

            }
        });

        function action(iThis, transportCode) {
            initAlertBox();
            removeAlertInputTransportCode();

            if (transportCode != "") {
                showAlertLoading();
                
                let flag = checkPastedTransportCode(transportCode, iThis.parent().parent().data('index'));

                if (flag) {
                    checkTransportCode(transportCode, iThis);
                }
            }

            initAlertBox();
        }

        function removeAlertInputTransportCode() {
            console.log('remove');
            $('.transport_code').removeClass('box-red');
        }

        $('.transport_code').bind("paste", function(e) {
            var iThis = $(this)
            var transportCode = e.originalEvent.clipboardData.getData('text');
           
            action(iThis, transportCode);
            
        } );

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
                    index = iThis.parent().parent().data('index');

                    // if transport code is exists
                    if (response.data == null) {
                        // status = ALERT_STATUS_DANGER;
                        // text = notChinaReceiveText(code);

                        // showAlertDanger(status, text);

                        setInputData('kg', index, "0");
                        setInputData('length', index, "0");
                        setInputData('width', index, "0");
                        setInputData('height', index, "0");
                        setInputData('advance_drag', index, "0");
                        setInputData('note', index, "MVD chưa bắn TQ nhận");

                        indexNextRow = index + 1
                        unDisabledRow(indexNextRow);
                        focusInput(indexNextRow, "transport_code");

                    } else {
                        fillInData(response.data, index);

                        indexNextRow = index + 1
                        unDisabledRow(indexNextRow);
                        focusInput(indexNextRow, "transport_code");
                    }
                }
            });
        }

        function fillInData(data, index) {
            initAlertBox();
            setInputData('transport_code', index, data.transport_code);
            setInputData('kg', index, data.kg);
            setInputData('length', index, data.length);
            setInputData('width', index, data.width);
            setInputData('height', index, data.height);
            setInputData('advance_drag', index, data.advance_drag);
            setInputData('note', index, data.note);
        }

        function notChinaReceiveText(code) {
            return "Mã vận đơn <b>" + code + "</b> chưa được nhập vào hệ thống. Vui lòng nhập só liệu.";
        }

        function thisCodeImported(code) {
            return "Mã vận đơn <b>" + code + "</b> đã được nhập ở ô đánh dấu đỏ. Vui lòng nhập lại só liệu.";
        }
        
        function setInputData(className, index, value) {
            $('tr[data-index="'+index+'"]').children().find('.' + className).val(value);
        }

        $(document).on('click', '.btn-delete-code', function () {

            let index = $(this).data('indexrow');
            initAlertBox();
            setInputData('transport_code', index, "");
            setInputData('kg', index, "");
            setInputData('length', index, "");
            setInputData('width', index, "");
            setInputData('height', index, "");
            setInputData('advance_drag', index, "");
            setInputData('note', index, "");

            focusInput(index, 'transport_code');
            removeAlertInputTransportCode();
        })

        function focusInput(index, className) {
            $('tr[data-index="'+index+'"]').children().find('.' + className).focus();
        }

        function checkPastedTransportCode(code, inputIndex) {
            let rs = true;
            $( ".transport_code" ).each(function( index ) {
                let value = $(this).val();
                let rowIndex = $(this).parent().parent().data('index');

                if (value != "" && value == code && inputIndex != rowIndex) {
                    $( this ).addClass('box-red');
                    status = ALERT_STATUS_DANGER;
                    text = thisCodeImported(code);

                    showAlertDanger(status, text);


                    rs = false;
                }
               
            }); 

            console.log(rs, 'check trung');

            return rs;
        }
    });
</script>