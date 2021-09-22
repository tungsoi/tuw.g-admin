<form action="{{ route('admin.complaints.storeAdminConfirmSuccess') }}" method="POST">
    {{ csrf_field() }}
    <h3>Bạn xác nhận khuyếu nại này đã xử lý xong ?</h3>
    <p>Hệ thống sẽ chuyển trạng thái khuyếu nại này sang "Nhân viên đặt hàng xác nhận xử lý khuyếu nại thành công", vui lòng đợi nhân viên kinh doanh xác nhận rằng đã thành công.</p>
    <input type="hidden" name="id" value="{{ $id }}">
    <button type="submit" class="btn btn-success">Xác nhận</button>
</form>