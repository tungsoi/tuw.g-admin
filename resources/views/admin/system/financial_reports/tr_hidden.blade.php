<tr class="hidden big-row" id="default-tr-hidden">
    <td style="width: 30px; text-align: center; vertical-align: middle;">
        <input type="checkbox" name="" id="" style="width: 30px; height: 25px;">
    </td>
    <td style="width: 50px">
        <input type="text" name="" id="" class="form-control big-row-order-number">
    </td>
    <td colspan="9">
        <input type="text" name="" id="" class="form-control big-row-title">
    </td>
    <td style="width: 30px; text-align: center; vertical-align: middle;">
        <button type="button" class="btn btn-sm btn-danger btn-remove-row">
            <i class="fa fa-trash"></i>
        </button>
    </td>
</tr>

<script>
    $(document).on('click', 'button.btn-remove-row', function () {
        $(this).parent().parent().remove();         
    });
</script>