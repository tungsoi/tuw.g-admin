<style>
    td {
        padding: 0px !important;
    }
    .hidden {
        display: none;
    }
    td, th {
        border-color: black !important;
    }
</style>
<table class="table table-bordered">
    <thead>
        <th></th>
        <th>STT</th>
        <th>CHỈ TIÊU</th>
        <th>MÃ DANH MỤC</th>
        <th>ORDER</th>
        <th>HAI BÀ TRƯNG</th>
        <th>THANH XUÂN</th>
        <th>HỒ CHÍ MINH</th>
        <th>BỘT</th>
        <th>TỔNG</th>
        <th>GHI CHÚ</th>
        <th></th>
    </thead>
    <tbody>
        @include('admin.system.financial_reports.tr_hidden')
        
    </tbody>
</table>

<div>
    <button type="button" class="btn btn-sm btn-warning" id="btn-add-bigrow-empty-table">+ Thêm mục lớn</button>
</div>

<script>
    $(document).ready(function () {
        const TBODY = $("table tbody");

        const BTN_BIGROW_EMPTY_TABLE = $('#btn-add-bigrow-empty-table');
        BTN_BIGROW_EMPTY_TABLE.on('click', function () {
            addBigRowToEmptyTable()
        });

        const TR_HIDDEN = $('#default-tr-hidden');
        const BIG_ROW_ELEMENT = $('.big-row');

        function addBigRowToEmptyTable() {
            let html = TR_HIDDEN.clone();
            html.attr('id', '');
            html.find('input.big-row-title').css('background', 'wheat');
            html.find('input.big-row-title').css('color', 'black');
            html.removeClass('hidden');
            html.appendTo(TBODY);
            html.find('input.big-row-title').focus();
            html.find('input.big-row-order-number').val(TBODY.children('tr').length-1);

            console.log(BIG_ROW_ELEMENT.size());
        }

        function randomColor() {
            return '#'+ ('000000' + Math.floor(Math.random()*16777215).toString(16)).slice(-6);
        }
    });
</script>