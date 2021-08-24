<?php

namespace App\Admin\Controllers\PurchaseOrder;

use App\Admin\Services\OrderService;
use App\Admin\Services\UserService;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\PurchaseOrder\PurchaseOrderItem;
use App\Models\PurchaseOrder\PurchaseOrderItemStatus;
use App\Models\System\Alert;
use App\Models\TransportOrder\TransportCode;
use App\User;
use DateTime;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Encore\Admin\Layout\Row;
use Encore\Admin\Layout\Column;
use Encore\Admin\Widgets\Box;

class ItemController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Sản phẩm mua hộ';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    public function grid()
    {
        $grid = new Grid(new PurchaseOrderItem());
        $grid->model()->orderBy('id', 'desc');

        $orderService = new OrderService();

        // Khach hang
        // if (Admin::user()->isRole('customer')) {
        //     $grid->model()->whereCustomerId(Admin::user()->id);
        // }

        $grid->filter(function($filter) {
            $filter->expand();
            $filter->disableIdFilter();
            $filter->column(1/4, function ($filter) {
                $filter->where(function ($query) {
                    $orderIds = PurchaseOrder::select('id')->where('order_number', 'like', "%".$this->input."%")->get()->pluck('id');
                    $query->whereIn('order_id', $orderIds);
                }, 'Mã đơn hàng', 'order_number');

                if (! Admin::user()->isRole('customer')) {
                    $filter->equal('customer_id', 'Mã khách hàng')->select(User::whereIsCustomer(1)->get()->pluck('symbol_name', 'id'));   
                } 

            });
            $filter->column(1/4, function ($filter) {
                $filter->equal('status', 'Trạng thái')->select(PurchaseOrderItemStatus::get()->pluck('name', 'id'));
            });

            $filter->column(1/4, function ($filter) {
                $filter->between('order_at', 'Ngày đặt hàng')->date();
            //     $filter->between('deposited_at', 'Ngày cọc')->date();

            //     if (! Admin::user()->isRole('customer')) {
            //         $filter->where(function ($query) {
            //             if ($this->input == '0') {
            //                 $dayAfter = (new DateTime(now()))->modify('-7 day')->format('Y-m-d H:i:s');
            //                 $query->where('deposited_at', '<=', $dayAfter)
            //             ->whereIn('status', []);
            //             }
            //         }, 'Tìm kiếm', '7days')->radio(['Đơn hàng chưa hoàn thành trong 7 ngày']);
            //     }
            });
            $filter->column(1/4, function ($filter) {
                $filter->between('success_at', 'Ngày xuất kho');
            }); 

            $filter->column(1/4, function ($filter) {
            //     if (! Admin::user()->isRole('customer')) {
            //         $service = new UserService();
            //         $filter->equal('supporter_id', 'Sale')->select($service->GetListSaleEmployee());

            //         $order_ids = DB::table('admin_role_users')->where('role_id', 4)->get()->pluck('user_id');
            //         $filter->equal('supporter_order_id', 'Order')->select(User::whereIsActive(1)->whereIn('id', $order_ids)->pluck('name', 'id'));
            //     }
            });

            Admin::style('
                #filter-box label {
                    padding: 0px !important;
                    padding-top: 10px;
                    font-weight: 600;
                    font-size: 12px;
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
        $grid->column('number', 'STT');
        if (strpos(url()->current(), route('admin.customer_items.index')) !== false) {
            $grid->order_number('Đơn hàng')->display(function () {
                $data = [
                    'order_number'  =>  [
                        'is_label'  =>  true,
                        'color'     =>  'info',
                        'text'      =>  $this->order->order_number
                    ],
                    'status'        =>  [
                        'is_label'  =>  true,
                        'color'     =>  $this->order->statusText->label,
                        'text'      =>  $this->order->statusText->name
                    ],
                    'timeline'        =>  [
                        'is_label'  =>  false,
                        'text'      =>  "<i>" . $this->order->getStatusTimeline() . "</i>"
                    ]
                ];
                return view('admin.system.core.list', compact('data'));
            });
        }
        $grid->status('Trạng thái')->display(function () {
            $data = [
                [
                'is_label'  =>  true,
                'color'     =>  $this->statusText->label,
                'text'      =>  $this->statusText->name
                ],
                [
                    'is_link'   =>  true,
                    'route'     =>  $this->product_link,
                    'text'      =>  'Link sản phẩm'
                ]
            ];
            return view('admin.system.core.list', compact('data'));
        });
        $grid->timeline('Timeline')->display(function () {
            $data = [
                'wait_order'        =>  [
                    'is_label'  =>  false,
                    'text'      =>  "Đặt hàng: "
                ],
                'success'        =>  [
                    'is_label'  =>  false,
                    'text'      =>  "Xuất kho: "
                ]
            ];
            return view('admin.system.core.list', compact('data'));
        });
        $grid->column('product_image', 'Ảnh sản phẩm')->lightbox(['width' => 70, 'height' => 70])->width(120);
        $grid->product_size('Kích thước');
        $grid->product_color('Màu');

        // trường hợp đang ở màn hình chi tiết đơn hàng mua hộ
        $flag_qty = false;
        if (strpos(url()->current(), route('admin.purchase_orders.index')) !== false) {
            $param = str_replace(route('admin.purchase_orders.index')."/", "", url()->current());
            if ($param != null) {
                $orderId = (int) $param;
                $status = PurchaseOrder::find($orderId)->status;

                if ($status == $orderService->getStatus('new-order') || $status == $orderService->getStatus('deposited')) {
                    $flag_qty = true;
                }
            }
        }

        if ($flag_qty) {
            $grid->qty('Số lượng')->editable();
        } else {
            $grid->qty('Số lượng');
        }
       
        $grid->qty_reality('Số lượng thực đặt');
        $grid->price('Đơn giá')->display(function () {
            $price_rmb = $this->price;
            $price_vnd = str_replace(",", "", $price_rmb) * $this->order->current_rate;

            $data = [
                'amount_rmb'   =>  [
                    'is_label'   =>  false,
                    'text'      =>  $price_rmb . " (tệ)"
                ],
                'amount_vnd'  =>  [
                    'is_label'  =>  false,
                    'text'      =>  "<i>= ". number_format($price_vnd) . " (vnd)" ."</i>"
                ]
            ];            
            return view('admin.system.core.list', compact('data'));
        })->style('text-align: right;');

        $grid->purchase_cn_transport_fee('Phí vận chuyển')->display(function () {
            $purchase_cn_transport_fee = $this->purchase_cn_transport_fee != null ? $this->purchase_cn_transport_fee : 0;
            $price_rmb = $purchase_cn_transport_fee;
            $price_vnd = str_replace(",", "", $price_rmb) * $this->order->current_rate;

            $data = [
                'amount_rmb'   =>  [
                    'is_label'   =>  false,
                    'text'      =>  $price_rmb . " (tệ)"
                ],
                'amount_vnd'  =>  [
                    'is_label'  =>  false,
                    'text'      =>  "<i>= ". number_format($price_vnd) . " (vnd)" ."</i>"
                ]
            ];            
            return view('admin.system.core.list', compact('data'));
        })->style('text-align: right;');
        $grid->column('total_price', 'Tổng tiền sản phẩm')->display(function () {

            $purchase_cn_transport_fee = $this->purchase_cn_transport_fee != null ? $this->purchase_cn_transport_fee : 0;
            $price_rmb = $this->qty_reality * $this->price + $purchase_cn_transport_fee ;
            $price_vnd = str_replace(",", "", $price_rmb) * $this->order->current_rate;

            $data = [
                'amount_rmb'   =>  [
                    'is_label'   =>  false,
                    'text'      =>  $price_rmb . " (tệ)"
                ],
                'amount_vnd'  =>  [
                    'is_label'  =>  false,
                    'text'      =>  "<i>= ". number_format($price_vnd) . " (vnd)" ."</i>"
                ]
            ];            
            return view('admin.system.core.list', compact('data'));
        })->style('text-align: right;');

        if ($flag_qty) {
            $grid->customer_note('Ghi chú')->style('max-width: 100px')->editable();
        } else {
            $grid->customer_note('Ghi chú')->style('max-width: 100px');
        }

        $grid->admin_note('Admin ghi chú')->style('max-width: 100px');

        Admin::script($this->script());

        return $grid;
    }

    public function form() {
        $form = new Form(new PurchaseOrderItem());
        
        $form->text('qty');
        $form->text('customer_note');

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
            $tools->disableList();
        });

        return $form;
    }

    public function script() {
        $route = route('admin.purchase_order_items.index');

        return <<<SCRIPT
            $('.column-qty a').each(function () {
                $(this).attr('data-url', "{$route}" + "/" + $(this).attr('data-pk'));
            });

            $('.column-customer_note a').each(function () {
                $(this).attr('data-url', "{$route}" + "/" + $(this).attr('data-pk'));
            });

            $(document).on('click', '.editable-submit', function () {
                setTimeout(function () {
                    location.reload();
                }, 1000);
            });
SCRIPT;
    }
    
}