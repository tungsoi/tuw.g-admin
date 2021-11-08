<span class="{{ $class_element }}-{{ $key }}">&nbsp;<i class="fa fa-spinner fa-spin"></i></span>

<script>

    // $( document ).ready(function() {
        $.ajax({
            url: {!! json_encode($url, JSON_HEX_TAG) !!},
            type: 'GET',
            dataType: "JSON",
            success: function (response)
            {
                if (response.status) {
                    $("." + {!! json_encode($class_element, JSON_HEX_TAG) !!} + "-" + {!! json_encode($key, JSON_HEX_TAG) !!})
                    .html(response.html);
                }
            }
        });
    // });

</script>