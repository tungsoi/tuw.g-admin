<form action="{{ route('admin.complaints.storeCustomerConfirmSuccess') }}" method="POST">
    {{ csrf_field() }}
    <h3>Bạn xác nhận khuyếu nại này đã được Order xử lý xong ?</h3>
    <p>Hệ thống sẽ chuyển trạng thái khuyếu nại này sang "Hoàn thành", vui lòng kiểm tra kỹ trước khi xác nhận.</p>
    <input type="hidden" name="id" value="{{ $id }}">
    <button type="submit" class="btn btn-success">Xác nhận</button>
</form>