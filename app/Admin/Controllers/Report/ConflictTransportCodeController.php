<?php

namespace App\Admin\Controllers\Report;

use App\Admin\Services\UserService;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\System\Transaction;
use App\Models\TransportOrder\TransportCode;
use App\User;
use Carbon\Carbon;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Illuminate\Support\Facades\DB;

class ConflictTransportCodeController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title;

    public function __construct()
    {
        $this->title = 'Đối soát mã vận đơn trên đơn hàng mua hộ';
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new PurchaseOrder());
        $grid->model()
        ->whereIn('status', [5,7,9])
        ->where('deposited_at', '>', Carbon::now()->subDays(30))
        ->where('transport_code', 'like', '%,%')
        ->orderBy('id', 'desc');

        $grid->disableFilter();
        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT')->style('text-align: center');

        $grid->order_number('Mã đơn hàng')->width(300);
        $grid->transport_code('Mã vận đơn trên đơn hàng')->display(function () {

            if ($this->transport_code != "") {
                $arr = explode(',', $this->transport_code);
                $html = "";
                foreach ($arr as $code) {
                    $flag = TransportCode::select('transport_code', 'status')->where('transport_code', $code)->first();
                    $class = 'default';
                    if (! $flag) {
                        $class = 'default';
                    } else {
                        if (in_array($flag->status, [1, 4, 5])) {
                            $class = 'primary';
                        } else if ($flag->status == 3) {
                            $class = 'success';
                        }
                    }

                    $html .= "<span class='label label-$class' style='margin-bottom: 5px !important;'>$code</span> &nbsp;";
                }

                return $html;
            }
        })->width(300);
        $grid->transport_code_item('Mã vận đơn trên sản phẩm')->display(function () {
            $arr = [];
            foreach ($this->items as $item) {
                $arr[$item->cn_code] = $item->cn_code;
            }

            $html = "";
            foreach ($arr as $code) {
                $flag = TransportCode::select('transport_code', 'status')->where('transport_code', $code)->first();
                $class = 'default';
                if (! $flag) {
                    $class = 'default';
                } else {
                    if (in_array($flag->status, [1, 4, 5])) {
                        $class = 'primary';
                    } else if ($flag->status == 3) {
                        $class = 'success';
                    }
                }

                $html .= "<span class='label label-$class' style='margin-bottom: 5px !important;'>$code</span> &nbsp;";
            }

            return $html;
        })->width(300);

        $grid->result('Kết quả kiểm tra')->display(function () {
            $arr_order = explode(',', $this->transport_code);
            $arr_item = [];
            foreach ($this->items as $item) {
                $arr_item[$item->cn_code] = $item->cn_code;
            }

            return array_diff($arr_order, $arr_item);
        })->label('danger');
        $grid->timeline('Timeline')->display(function () {
            $data = [];

            if ($this->created_at != null) {
                $data[] = [
                    'is_label'   =>  false,
                    'text'      =>   "1. Ngày tạo: ". ($this->created_at != null ? date('H:i | d-m-Y', strtotime($this->created_at)) : "")
                ];
            }

            if ($this->deposited_at != null) {
                $data[] = [
                    'is_label'   =>  false,
                    'text'      =>   "2. Ngày cọc: ". ($this->deposited_at != null ? date('H:i | d-m-Y', strtotime($this->deposited_at)) : "")
                ];
            }

            if ($this->order_at != null) {
                $data[] = [
                    'is_label'   =>  false,
                    'text'      =>   "3. Ngày đặt hàng: ". ($this->order_at != null ? date('H:i | d-m-Y', strtotime($this->order_at)) : "")
                ];
            }

            if ($this->vn_receive_at != null) {
                $data[] = [
                    'is_label'   =>  false,
                    'text'      =>   "4. Ngày về VN: ". ($this->vn_receive_at != null ? date('H:i | d-m-Y', strtotime($this->vn_receive_at)) : "")
                ];
            }

            if ($this->success_at != null) {
                $data[] = [
                    'is_label'   =>  false,
                    'text'      =>   "5. Ngày thành công: ". ($this->success_at != null ? date('H:i | d-m-Y', strtotime($this->success_at)) : "")
                ];
            }
            if ($this->cancle_at != null) {
                $data[] = [
                    'is_label'   =>  false,
                    'text'      =>   "6. Ngày huỷ: ". ($this->cancle_at != null ? date('H:i | d-m-Y', strtotime($this->cancle_at)) : "")
                ];
            }

            return view('admin.system.core.list', compact('data'));
        });
        $grid->disableActions();
        $grid->disableCreateButton();
        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->disableExport();

        return $grid;
    }
}