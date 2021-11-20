<?php

namespace App\Admin\Controllers\PurchaseOrder;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Grid;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\System\Transaction;
use App\Models\TransportOrder\TransportCode;

class TodayController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Đối soát đơn hàng mua hộ về trong ngày';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new PurchaseOrder());

        $today = date('Y-m-d', strtotime(now()));
        $grid->model()->whereBetween('vn_receive_at', [$today." 00:00:01", $today." 23:59:59"])->orderBy('status', 'asc')
        ->with('items')
        ->with('statusText')
        ->with('customer')
        ->with('orderEmployee')
        ->with('warehouse');


        $grid->header(function () use ($today) {
            $data = PurchaseOrder::select('id', 'order_number', 'status')->whereBetween('vn_receive_at', [$today." 00:00:01", $today." 23:59:59"])->orderBy('id', 'desc')->get();
            $html = "<h4><b>";
            $html .= "Danh sách đơn hàng mua hộ về trong ngày - " . date('Y-m-d', strtotime(now()));
            $html .= "<br><br> - Tổng số đơn: " . $data->count();
            $html .= "<br> - Tổng số đơn Đã về Việt Nam: " . $data->where('status', 7)->count();
            $html .= "<br> - Tổng số đơn Hoàn thành: " . $data->where('status', 9)->count();
            $html .= "</b></h4>";
            return $html;
        });
        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT');
        $grid->order_number('Mã đơn hàng')->display(function () {
            $data = [
                'order_number'   =>  [
                    'is_label'  =>  true,
                    'color'     =>  'primary',
                    'text'      =>  "<b>".$this->order_number."</b>"
                ],
                'current_rate'  =>  [
                    'is_label'  =>  false,
                    'color'     =>  'info',
                    'text'      =>  "TG: ".number_format($this->current_rate)
                ],
                'total_item'    =>  [
                    'is_label'  =>  false,
                    'text'      =>  "". $this->items->where('status', '!=', 4)->count()." link, ". $this->totalItems() . " sp"
                ],
                'order_type'    =>  [
                    'is_label'  =>  true,
                    'color'     =>  'default',
                    'text'      =>  $this->order_type
                ]
            ];
            return view('admin.system.core.list', compact('data'));
        });
        $grid->status('Trạng thái / Khách hàng')->display(function () {
            $data = [
                'status'        =>  [
                    'is_label'  =>  true,
                    'color'     =>  $this->statusText->label,
                    'text'      =>  $this->statusText->name . $this->countItemFollowStatus() . ($this->status == 7 ? $this->countProductFollowStatus() : null)
                ],
                'shop_name'        =>  [
                    'is_label'  =>  false,
                    'text'      =>  "Shop: ".$this->shop_name
                ]
            ];
            return view('admin.system.core.list', compact('data'));
        })->style('max-width: 200px');
        $grid->employee('Nhân viên')->display(function () {
            $sale = $this->customer->saleEmployee ? $this->customer->saleEmployee->name : null;
            $sale_link = $this->customer->saleEmployee ? $this->customer->saleEmployee->phone_number : null;

            $order = $this->orderEmployee ? $this->orderEmployee->name : null;
            $order_link = $this->orderEmployee ? $this->orderEmployee->phone_number : null;

            $warehouse = $this->warehouse ? $this->warehouse->name : null;
        
            $data = [
            'sale'   =>  [
                'is_label'   =>  false,
                'text'      =>  "- Sale: " . $sale,
                'route'     =>  "https://zalo.me/" .  $sale_link
            ],
            'order'  =>  [
                'is_label'  =>  false,
                'text'      =>  "- Order: " . $order,
                'route'     =>  "https://zalo.me/" .  $order_link
            ],
            'warehouse'    =>  [
                'is_label'  =>  false,
                'text'      =>  "- Kho: " . $warehouse
            ]
        ];
            return view('admin.system.core.list', compact('data'));
        })->width(150);
        $grid->transport_code('Mã vận đơn')->display(function () {

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
        })->width(150);
        $grid->customer_note('Khách hàng ghi chú')->style('max-width: 100px');
        $grid->admin_note('Admin ghi chú')->style('max-width: 100px');
        $grid->internal_note('Ghi chú nội bộ')->style('max-width: 100px');
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
        $grid->transaction('Giao dịch')->display(function () {
            $transaction = Transaction::where('content', "Thanh toán đơn hàng mua hộ. Mã đơn hàng $this->order_number")->first();

            if ($transaction) {
                return "* ".$transaction->content . " <br> <br> * " . number_format($transaction->money) . " <br> <br>* " . $transaction->created_at;
            }

            if ($this->status == 9) {
                return "<span style='color:red'>Chưa có giao dịch thanh toán</span>";
            } else {
                $flag_item = $this->countItemFollowStatus('boolean');
                $flag_product = $this->countProductFollowStatus('boolean');
                if ($flag_item && $flag_product)
                {
                    return "<span style='color:red'> <i class='fa fa-spinner fa-spin' style='color: red'></i> Đang chờ hệ thống thanh toán</span>";
                } else {
                    return "<span style='color:blue'> Mã vận đơn hoặc hàng chưa về đủ</span>";
                }
            }

            
        });
        $grid->disableBatchActions();
        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->disableFilter();
        $grid->disableColumnSelector();
        $grid->paginate(10);
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableEdit();
            $actions->disableDelete();
            $actions->disableView();

            $route = route('admin.purchase_orders.show', $this->row->id);
            $actions->append('
                <a target="_blank" href="'.$route.'" class="grid-row-view btn btn-xs btn-primary" data-toggle="tooltip" title="" data-original-title="Xem chi tiết">
                    <i class="fa fa-eye"></i>
                </a>
            ');
        });
        return $grid;
    }
}
