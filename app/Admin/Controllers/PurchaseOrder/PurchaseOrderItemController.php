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

class PurchaseOrderItemController extends AdminController
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
        $grid->model()->whereNotNull('order_id')->orderBy('id', 'desc');

        $orderService = new OrderService();

        // Khach hang
        $orderIds = PurchaseOrder::whereCustomerId(Admin::user()->id)->pluck('id');
        if (Admin::user()->isRole('customer')) {
            $grid->model()->whereIn('order_id', $orderIds);
        }

        $grid->filter(function($filter) use ($orderService) {
            $filter->expand();
            $filter->disableIdFilter();
            $filter->column(1/3, function ($filter) {
                $filter->where(function ($query) {
                    $orderIds = PurchaseOrder::select('id')->where('order_number', 'like', "%".$this->input."%")->get()->pluck('id');
                    $query->whereIn('order_id', $orderIds);
                }, 'Mã đơn hàng', 'order_number');

                if (! Admin::user()->isRole('customer')) {
                    $filter->equal('customer_id', 'Mã khách hàng')->select(User::whereIsCustomer(1)->get()->pluck('symbol_name', 'id'));   
                } 

            });
            $filter->column(1/3, function ($filter) use ($orderService) {
                $filter->equal('status', 'Trạng thái')
                    ->select(
                        PurchaseOrderItemStatus::whereIn(
                            'code', 
                            ['in_order', 'wait_order', 'out_stock']
                        )
                        ->get()
                        ->pluck('name', 'id')
                    );
                
                if (! Admin::user()->isRole('customer')) {
                    $filter->like('cn_order_number', 'Mã giao dịch');
                }
            });

            $filter->column(1/3, function ($filter) {
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
        if (strpos(url()->current(), route('admin.purchase_order_items.index')) !== false) {
            $grid->order_number('Mã đơn hàng')->display(function () {
                $data = [
                    'order_number'  =>  [
                        'is_label'  =>  true,
                        'color'     =>  'primary',
                        'text'      =>  $this->order ? $this->order->order_number : "--"
                    ],
                    'order_number_status'  =>  [
                        'is_label'  =>  true,
                        'color'     =>  $this->order ? $this->order->statusText->label : "",
                        'text'      =>  $this->order ? $this->order->statusText->name : ""
                    ],
                ];

                if (! Admin::user()->isRole('customer')) {

                    $symbol_name = "MKH: --";
                    $sale_staff = "--";
                    $order_staff = "--";

                    if ($this->order != null) {
                        if ($this->order->customer != null) {
                            $symbol_name = $this->order->customer->symbol_name;

                            if ($this->order->customer->saleEmployee != null) {
                                $sale_staff = $this->order->customer->saleEmployee->name ?? "--";
                            }
                        }

                        if ($this->order->orderEmployee != null) {
                            $order_staff = $this->order->orderEmployee->name ?? "--";
                        }
                        
                    }

                    $data[] = [
                        'is_label'  =>  false,
                        'color'     =>  'info',
                        'text'      =>  $symbol_name
                    ];

                    $data[] = [
                        'is_label'  =>  false,
                        'text'      =>  "Sale: " .$sale_staff
                    ];

                    $data[] = [
                        'is_label'  =>  false,
                        'text'      =>  "Order: " .$order_staff
                    ];
                }
                return view('admin.system.core.list', compact('data'));
            })->width(150);
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
        $grid->column('product_image', 'Ảnh')->lightbox(['width' => 70, 'height' => 70])->width(100);
        $grid->product_size('Kích thước')->style('text-align: right; max-width: 100px;');
        $grid->product_color('Màu')->style('text-align: right; max-width: 100px;');

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
            $grid->qty('Số lượng lên đơn')->editable()->style('text-align: right; max-width: 100px;');
        } else {
            $grid->qty('Số lượng lên đơn')->style('text-align: right; max-width: 100px;');
        }
       
        $grid->qty_reality('Số lượng thực đặt')->style('text-align: right; max-width: 100px;');
        $grid->price('Đơn giá')->display(function () {
            try {
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
            } catch (\Exception $e) {
                return "<span style='color: red'> Lỗi $this->id</span>";
            }
        })->style('text-align: right; max-width: 150px;');

        $grid->purchase_cn_transport_fee('Phí vận chuyển')->display(function () {
            try {
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
            } catch (\Exception $e) {
                return "<span style='color: red'> Lỗi $this->id</span>";
            }
        })->style('text-align: right; max-width: 150px;');
        $grid->column('total_price', 'Tổng tiền sản phẩm')->display(function () {

            try {
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
            } catch (\Exception $e) {
                return "<span style='color: red'> Lỗi $this->id</span>";
            }
        })->style('text-align: right; max-width: 150px;');

        if ($flag_qty) {
            $grid->customer_note('Khách hàng ghi chú')->style('max-width: 100px')->editable();
        } else {
            $grid->customer_note('Khách hàng ghi chú')->style('max-width: 100px');
        }

        if (Admin::user()->isRole('customer')) {
            $grid->admin_note('Admin ghi chú')->style('max-width: 100px');
        } else {
            $grid->admin_note('Admin ghi chú')->editable()->style('max-width: 100px');
            $grid->cn_order_number('Mã giao dịch')->editable();
        }

        Admin::script($this->script());

        $grid->disableCreateButton();
        $grid->disableBatchActions();
        $grid->disableExport();
        $grid->disableColumnSelector();

        // if (Admin::user()->isRole('customer')) {
        //     $grid->disableActions();
        // }

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
            $actions->disableDelete();

            if (($this->row->order == null) || ! in_array($this->row->order->status, [2, 4])) {
                $actions->disableEdit();
            }

        });

        return $grid;
    }

    public function form() {
        $form = new Form(new PurchaseOrderItem());
        
        $form->currency('qty', 'Số lượng lên đơn')->digits(0)->symbol('');
        if (Admin::user()->isRole('customer')) {
            $form->text('customer_note', 'Khách hàng ghi chú');
        } else {
            $form->currency('qty_reality', 'Số lượng thực đặt')->digits(0)->symbol('');
            $form->text('admin_note', 'Admin ghi chú');
            $form->textarea('cn_order_number', 'Mã giao dịch');
        }

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