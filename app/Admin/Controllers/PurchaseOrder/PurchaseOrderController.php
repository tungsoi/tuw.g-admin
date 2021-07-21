<?php

namespace App\Admin\Controllers\PurchaseOrder;

use App\Admin\Services\OrderService;
use App\Admin\Services\UserService;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\PurchaseOrder\PurchaseOrderItem;
use App\Models\System\Alert;
use App\User;
use DateTime;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Đơn hàng mua hộ';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new PurchaseOrder());
        $grid->model()->orderBy('id', 'desc');

        $grid->filter(function($filter) {
            $filter->expand();
            $filter->disableIdFilter();
            $filter->column(1/4, function ($filter) {
                $filter->like('order_number', 'Mã đơn hàng');
                $filter->equal('customer_id', 'Mã khách hàng')->select(User::whereIsCustomer(1)->get()->pluck('symbol_name', 'id'));    
                $filter->equal('status', 'Trạng thái')->select([]);
            });
            $filter->column(1/4, function ($filter) {
                $filter->between('created_at', 'Ngày tạo')->date();
                $filter->between('deposited_at', 'Ngày cọc')->date();
                $filter->where(function ($query) {
                    if ($this->input == '0') {
                        $dayAfter = (new DateTime(now()))->modify('-7 day')->format('Y-m-d H:i:s');
                        $query->where('deposited_at', '<=', $dayAfter)
                        ->whereIn('status', []);
                    }
                }, 'Tìm kiếm', '7days')->radio(['Đơn hàng chưa hoàn thành trong 7 ngày']);
            });
            $filter->column(1/4, function ($filter) {
                $filter->between('order_at', 'Ngày đặt hàng');
                $filter->between('success_at', 'Ngày hoàn thành');
            }); 
            $filter->column(1/4, function ($filter) {

                $service = new UserService();
                $filter->equal('supporter_id', 'Sale')->select($service->GetListSaleEmployee());

                $order_ids = DB::table('admin_role_users')->where('role_id', 4)->get()->pluck('user_id');
                $filter->equal('supporter_order_id', 'Order')->select(User::whereIsActive(1)->whereIn('id', $order_ids)->pluck('name', 'id'));
            });

            Admin::style('
                #filter-box label {
                    padding: 0px !important;
                    padding-top: 10px;
                }
                #filter-box .col-sm-2 {
                    width: 100% !important;
                    text-align: left;
                    padding: 0px 15px 3px 15px !important;
                }
                #filter-box .col-sm-8 {
                    width: 100% !important;
                }
            ');
        });

        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT (1)');
        $grid->order_number('Mã đơn hàng (2)')->display(function () {
            $data = [
                'order_number'   =>  [
                    'is_label'  =>  true,
                    'color'     =>  'primary',
                    'text'      =>  $this->order_number
                ],
                'status'        =>  [
                    'is_label'  =>  true,
                    'color'     =>  $this->statusText->label,
                    'text'      =>  $this->statusText->name
                ],
                'current_rate'  =>  [
                    'is_label'  =>  false,
                    'color'     =>  'info',
                    'text'      =>  "Tỷ giá: ".number_format($this->current_rate)
                ],
                'total_item'    =>  [
                    'is_label'  =>  false,
                    'text'      =>  "<b>".$this->totalItems() . " sản phẩm </b>"
                ]
            ];
            return view('admin.system.core.list', compact('data'));
        });
        $grid->customer_id('Khách hàng (3)')->display(function () {
            $data = [
                'customer_id'   =>  [
                    'is_label'  =>  true,
                    'color'     =>  'primary',
                    'text'      =>  $this->customer->symbol_name
                ],
                'customer_wallet'  =>  [
                    'is_label'  =>  false,
                    'color'     =>  'info',
                    'is_link'   =>  true,
                    'text'      =>  number_format($this->customer->wallet) . " (VND)",
                    'route'     =>  route('admin.customers.transactions', $this->customer_id)
                ],
                'zalo'      =>  [
                    'is_link'   =>  true,
                    'text'      =>  "Zalo",
                    'route'     =>  "https://zalo.me/" .  $this->customer->phone_number
                ]
            ];            
            return view('admin.system.core.list', compact('data'));
        });
        $grid->employee('Nhân viên (4)')->display(function () {
            $sale = $this->customer->saleEmployee ? $this->customer->saleEmployee->name : null;
            $sale_link = $this->customer->saleEmployee ? $this->customer->saleEmployee->phone_number : null;

            $order = $this->orderEmployee ? $this->orderEmployee->name : null;
            $order_link = $this->orderEmployee ? $this->orderEmployee->phone_number : null;

            $data = [
                'sale'   =>  [
                    'is_link'   =>  true,
                    'text'      =>  "Sale: " . $sale,
                    'route'     =>  "https://zalo.me/" .  $sale_link
                ],
                'order'  =>  [
                    'is_link'  =>  true,
                    'text'      =>  "Order: " .$order,
                    'route'     =>  "https://zalo.me/" .  $order_link
                ],
                'warehouse'    =>  [
                    'is_label'  =>  false,
                    'text'      =>  "Kho: ".$this->warehouse->name
                ]
            ];            
            return view('admin.system.core.list', compact('data'));
        });

        $grid->sumItemPrice('Tổng giá sản phẩm (6)')->display(function () {
            return $this->sumItemPrice();
        })->width(70)->style('text-align: right');

        $grid->purchase_order_service_fee('Phí dịch vụ (7)')->editable()->width(50)->style('text-align: right');

        $grid->sumShipFee('Phí vận chuyển (8)')->display(function () {
            return $this->sumShipFee();
        })->width(50)->style('text-align: right');

        $grid->amount('Tổng giá cuối <br> (9) = (6 + 7 + 8)')->display(function () {
            $data = [
                'amount_rmb'   =>  [
                    'is_label'   =>  false,
                    'text'      =>  "<b>" .$this->amount() . "</b>"
                ],
                'amount_vnd'  =>  [
                    'is_label'  =>  false,
                    'text'      =>  "<i>= ".number_format(str_replace(",", "", $this->amount()) * $this->current_rate) . " (VND)" ."</i>"
                ]
            ];            
            return view('admin.system.core.list', compact('data'));
        })->style('text-align: right; width: 150px;');

        $grid->sumItemWeight('Tổng cân (10)')->display(function () {
            return $this->sumItemWeight();
        })->width(50)->style('text-align: right');

        $grid->deposited_at('Đã cọc (11)')->display(function () {
            return number_format($this->deposited_at);
        })->style('text-align: right; width: 100px;');

        $grid->transport_code('Mã vận đơn')->editable('textarea')->width(150);

        $grid->final_payment('Tổng thanh toán (12)')->editable()->width(50)->style('text-align: right');

        $grid->created_at('Timeline (13)')->display(function () {
            return "Xem chi tiết";
        })->expand(function ($model) {
            $info = [
                [
                    "Tạo đơn",
                    date('H:i | d-m-Y', strtotime($this->created_at)),
                    ""
                ],
                [
                    "Đặt cọc",
                    $this->deposited_at != ""
                        ? date('H:i | d-m-Y', strtotime($this->deposited_at))
                        : null,
                    ""
                ],
                [
                    "Đặt hàng",
                    $this->order_at != ""
                        ? date('H:i | d-m-Y', strtotime($this->order_at))
                        : null,
                    ""
                ],
                [
                    "Thành công",
                    $this->success_at != ""
                        ? date('H:i | d-m-Y', strtotime($this->success_at))
                        : null,
                    ""
                ],
                [
                    "Huỷ đơn",
                    $this->cancle_at != ""
                        ? date('H:i | d-m-Y', strtotime($this->cancle_at))
                        : null,
                    ""
                ]
            ];
        
            return new Table(['Ngày', 'Thời gian', 'Người thực hiện'], $info);
        })->width(100);
        
        $grid->disableExport();
        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->disablePagination();
        $grid->disablePerPageSelector();
        $grid->paginate(20);
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
        });

        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Alert);

        $form->text('title', "Tiêu đề")->rules(['required']);
        $form->summernote('content', "Nội dung")->rules(['required']);
        $form->hidden('created_user_id')->default(Admin::user()->id);
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });

        return $form;
    }
}
