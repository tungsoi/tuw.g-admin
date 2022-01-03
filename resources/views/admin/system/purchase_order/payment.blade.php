<br>
<div>
    <input type="radio" id="all_weight" name="all_type" value="all_weight" style="width: 20px !important;">
    <label for="all_weight">Tất cả tính cân</label><br>
    <input type="radio" id="all_m3" name="all_type" value="all_m3" style="width: 20px !important;">
    <label for="all_m3">Tất cả tính khối</label><br>
</div>
<table class="table table-bordered">
    <thead>
        <th>STT</th>
        <th>Mã vận đơn</th>
        <th>Mã khách hàng</th>
        <th>Loại thanh toán</th>
        <th>KG</th>
        <th>Dài</th>
        <th>Rộng</th>
        <th>Cao</th>
        <th>M3</th>
        <th>V/6000</th>
        <th>Ứng kéo</th>
    </thead>
    <tbody>
        @php
            $total_kg = 0;
        @endphp

        @foreach ($transportCodes as $key => $code)
            @php
                $total_kg += str_replace(".0", "", $code->kg);
            @endphp

            <tr>
                <td align="center">
                    {{ $key+1 }}
                    <input type="hidden" name="transport_code_id[]" value="{{ $code->id }}">
                </td>
                <td style="width: 300px;">{{ $code->transport_code }}</td>
                <td>{{ $code->customer_code_input }}</td>
                <td>
                    <select class="payment_type form-control" 
                    style="width: 100%;" name="payment_type[]" 
                    data-kg="{{ $code->kg }}" 
                    data-m3="{{ ($code->m3 == "" || $code->m3 == "0.00") ? $code->m3_cal() : $code->m3 }}" 
                    data-v="{{ $code->v() }}">
                        <option value="1" selected="">Khối lượng</option>
                        <option value="-1">M3</option>
                        <option value="0">V/6000</option>
                    </select>
                </td>
                <td align="right">{{ str_replace(".0", "", $code->kg) }}</td>
                <td align="right">{{ $code->length }}</td>
                <td align="right">{{ $code->width }}</td>
                <td align="right">{{ $code->height }}</td>
                <td align="right">{{ ($code->m3 == "" || $code->m3 == "0.00") ? $code->m3_cal() : $code->m3 }}</td>
                <td align="right">{{ str_replace(".00", "", $code->v()) }}</td>
                <td align="right">{{ $code->advance_drag }}</td>
            </tr>
        @endforeach
        <tr>
            <td class=""></td>
            <td class=""></td>
            <td class=""></td>
            <td class=""></td>
            <td class="" align="right">{{ $total_kg }}</td>
            <td class=""></td>
            <td class=""></td>
            <td class=""></td>
            <td class=""></td>
            <td class=""></td>
            <td class=""></td>
        </tr>
    </tbody>
</table>

<script src="https://cdnjs.cloudflare.com/ajax/libs/autonumeric/4.1.0/autoNumeric.min.js"></script>
<script>
    $( document ).ready(function() {
        $(function() {
            new AutoNumeric.multiple('.sum_kg', {
                decimalPlaces: 0
            });

            new AutoNumeric.multiple('.sum_volumn', {
                decimalPlaces: 0
            });

            new AutoNumeric.multiple('.sum_cublic_meter', {
                decimalPlaces: 0
            });
        });
    
        $(".btn-success").toggle();
        $(".payment_user_id").on("select2:select", function (e) { 
            var userId = $(e.currentTarget).val();

            $('.loading-overlay').toggle();
            $.ajax({
                url: '/admin/customers/' + userId + '/find',
                type: 'GET',
                dataType: "JSON",
                success: function (response)
                {
                    $('.loading-overlay').toggle();
                    $('.customer_select_id').val(response.data.id);
                    $('#customer-info-payment #payment_customer_wallet').html(response.data.wallet);
                    $('#customer-info-payment #payment_customer_wallet_weight').html(response.data.wallet_weight);
                    $('#customer-info-payment #price_kg').html(response.data.default_price_kg);
                    $('#customer-info-payment #price_m3').html(response.data.default_price_m3);
                    $('#customer-info-payment .payment_customer_wallet_weight').val(response.data.wallet_weight);
                    $('#customer-info-payment #customer_note').html(response.data.note);
                    $('#customer_phone_number').html(response.data.phone_number);
                    $('a#zalo-contact').attr('href', 'https://zalo.me/' + response.data.phone_number);
                }
            });
        });

        $(".payment_type").on("change", function () {
            var payment_type = $(this).val();

            calculator();
        });

        $(".discount_type").on("change", function () {
            calculator();
        });

        $(".discount_value").on('keyup', function (e)
        {
            calculator();
        });

        $(".wallet_weight").on("change", function () {
            var customer_id = $('.customer_select_id').val();

            if (customer_id == "") {
                $.admin.toastr.error('Vui lòng chọn Khách hàng thanh toán', '', {positionClass: 'toast-top-center'});
                $(".wallet_weight").val(0);
            } else {
                calculator();
            }
        });

        $(".sum_volumn").on('keyup', function (e)
        {
            calculator();
        });

        $(".sum_cublic_meter").on('keyup', function (e)
        {
            calculator();
        });

        $(".sum_kg").on('keyup', function (e)
        {
            calculator();
        });

        function calculator() {
            console.log('calculator');
            var amount_kg = parseFloat(0);
            var amount_m3 = parseFloat(0);
            var amount_v = parseFloat(0);

            $(".payment_type").each(function(index){
                if ($(this).has('option:selected')) {
                    var payment_type = $(this).val();

                    var kg = $(this).data('kg');
                    var m3 = $(this).data('m3');
                    var v = $(this).data('v');

                    if (payment_type == -1) {
                        // tinh khoi
                        amount_m3 += parseFloat(m3);
                    } else if (payment_type == 0) {
                        // tính thể tích
                        amount_v += parseFloat(v);
                    } else if (payment_type == 1) {
                        // tính cân
                        amount_kg += parseFloat(kg);
                    }
                }
            });

            // giam tru can nang
            var discount_type = $(".discount_type").val();
            var discount_value = $(".discount_value").val();
            if (discount_value == "") {
                discount_value = 0;
            }

            if (discount_type == 1) {
                // giam di
                amount_kg -= parseFloat(discount_value);
            } else {
                // tang len
                amount_kg += parseFloat(discount_value);
            }

            // vi can
            var wallet_weight = $('.wallet_weight').val();
            if (wallet_weight == 1) {
                // tru vao vi can
                customer_wallet_weight = $(".payment_customer_wallet_weight").val();
                customer_wallet_weight = parseFloat(customer_wallet_weight).toFixed(1);

                if (customer_wallet_weight > amount_kg) {
                    // du vi can de tru = amount_kg
                    $('#payment_customer_wallet_weight').html(customer_wallet_weight + " - " + amount_kg.toFixed(1) );
                    $('#payment_customer_wallet_weight').css('color', 'red');

                    $('.payment_customer_wallet_weight_used').val(amount_kg);

                    amount_kg = parseFloat(0);
                } else {
                    // customer_wallet_weight
                    $('#payment_customer_wallet_weight').html(customer_wallet_weight + " - " + amount_kg.toFixed(1) );
                    $('#payment_customer_wallet_weight').css('color', 'red');

                    $('.payment_customer_wallet_weight_used').val(customer_wallet_weight);

                    amount_kg -= customer_wallet_weight;
                }
                console.log('customer_wallet_weight: ' + customer_wallet_weight);
            } else {
                // khong tru
            }
            

            // show html

            amount_kg = amount_kg.toFixed(1);
            amount_m3 = amount_m3.toFixed(3);
            amount_v = amount_v.toFixed(2);

            $('.lb-sum-kg').html(amount_kg);
            $('.count_kg').val(amount_kg);

            $('.lb-sum-cublic-meter').html(amount_m3);
            $('.count_cublic_meter').val(amount_m3);

            $('.lb-sum-volumn').html(amount_v);
            $('.count_volumn').val(amount_v);

            console.log('amount_kg: ' + amount_kg);
            console.log('amount_m3: ' + amount_m3);
            console.log('amount_v: ' + amount_v);

            // calculator money
            calculatorMoney();
        }
        
        function calculatorMoney() {
            var amount_kg = $('.count_kg').val();
            if (amount_kg == "") {
                amount_kg = parseFloat(0);
            }

            var amount_m3 = $('.count_cublic_meter').val();
            if (amount_m3 == "") {
                amount_m3 = parseFloat(0);
            }
            
            var amount_v = $('.count_volumn').val();
            if (amount_v == "") {
                amount_v = parseFloat(0);
            }

            var price_kg = $('.sum_kg').val();
            if (price_kg == "") {
                price_kg = 0;
            } else {
                price_kg = price_kg.replace(/,/g, "");
            }

            var price_m3 = $('.sum_cublic_meter').val();
            if (price_m3 == "") {
                price_m3 = 0;
            } else {
                price_m3 = price_m3.replace(/,/g, "");
            }

            var price_v = $('.sum_volumn').val();
            if (price_v == "") {
                price_v = 0;
            } else {
                price_v = price_v.replace(/,/g, "");
            }

            var total_kg = parseInt(amount_kg * price_kg);
            var total_m3 = parseInt(amount_m3 * price_m3);
            var total_v = parseInt(amount_v * price_v);
            var total_advance_drag_str = $("input.advan_vnd").val();
            if (total_advance_drag_str == "") {
                total_advance_drag_str = 0;
            }
            var total_advance_drag = parseInt(total_advance_drag_str);

            var owed_purchase_order = 0;
            if( $('input.owed_purchase_order').length )         // use this if you are using id to check
            {
                var owed_purchase_order_input = $('input.owed_purchase_order').val();
                owed_purchase_order = parseInt(owed_purchase_order_input);
            }

            var total_bill = parseInt(total_kg + total_m3 + total_v + total_advance_drag + owed_purchase_order);

            console.log('amount_kg: ' + amount_kg + " - price: " + price_kg + " - total: " + total_kg);
            console.log('amount_m3: ' + amount_m3 + " - price: " + price_m3 + " - total: " + total_m3);
            console.log('amount_v: ' + amount_v + " - price: " + price_v + " - total: " + total_v);
            console.log("advance_drag: " + total_advance_drag);

            // show to input
            $('.total_kg').val(number_format(total_kg));
            $('.total_cublic_meter').val(number_format(total_m3));
            $('.total_volumn').val(number_format(total_v));
            $('span.total_money').html(number_format(total_bill));
            $('input.total_money').val(total_bill);
        }

        function number_format(number, decimals, dec_point, thousands_sep) {
            // Strip all characters but numerical ones.
            number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
            var n = !isFinite(+number) ? 0 : +number,
                prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
                dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
                s = '',
                toFixedFix = function (n, prec) {
                    var k = Math.pow(10, prec);
                    return '' + Math.round(n * k) / k;
                };
            // Fix for IE parseFloat(0.55).toFixed(0) = 0;
            s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            }
            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }
            return s.join(dec);
        }

        $(document).on('click', '#btn-re-cal', function () {
            $('.loading-overlay').toggle();
            calculator();
            setTimeout(() => {
                $('.loading-overlay').toggle(); 
                $(".btn-success").show();
            }, 1000);
        });

        jQuery(function($) {
            $(window).scroll(function fix_element() {
                $('#customer-info-payment').css(
                $(window).scrollTop() > 100
                    ? { 'position': 'fixed', 'top': '10px', 'background': 'white', 'z-index': '1000', 'width': '455px'}
                    : { 'position': 'relative', 'top': 'auto' }
                );
                return fix_element;
            }());

            // let payment_customer_div = $('label[for="payment_user_id"]').parent().parent();

            // $(window).scroll(function fix_element() {
            //     payment_customer_div.css(
            //     $(window).scrollTop() > 100
            //         ? { 'position': 'fixed', 'top': '10px', 'background': 'white', 'z-index': '1000', 'width': '455px', 'border-color' : 'black' }
            //         : { 'position': 'relative', 'top': 'auto' }
            //     );
            //     return fix_element;
            // }());

            // let payment_type = $('input[name="order_type"]').parent();

            // $(window).scroll(function fix_element() {
            //     payment_type.css(
            //     $(window).scrollTop() > 100
            //         ? { 'position': 'fixed', 'top': '10px', 'background': 'white', 'z-index': '1000', 'width': '455px', 'border-color' : 'black' }
            //         : { 'position': 'relative', 'top': 'auto' }
            //     );
            //     return fix_element;
            // }());
        });

        $('#all_weight').click(function() {
            if($('#all_weight').is(':checked')) { 
                $('select.payment_type').val(1);
                console.log('all_weight');
            }
        });

        $('#all_m3').click(function() {
            if($('#all_m3').is(':checked')) { 
                console.log('all_m3'); 
                $('select.payment_type').val(-1);
            }
        });
    });
</script>