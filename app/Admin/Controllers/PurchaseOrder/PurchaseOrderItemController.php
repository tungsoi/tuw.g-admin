<?php

namespace App\Admin\Controllers\PurchaseOrder;

use App\Admin\Actions\PurchaseOrder\AddTransportCode;
use App\Admin\Actions\PurchaseOrder\ConfirmOrderItem;
use App\Admin\Actions\PurchaseOrder\ConfirmVnReceiveItem;
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
        $grid->model()->whereNotNull('order_id')->orderBy('order_at', 'desc');

        $orderService = new OrderService();

        // Khach hang
        $orderIds = PurchaseOrder::whereCustomerId(Admin::user()->id)->pluck('id');
        if (Admin::user()->isRole('customer')) {
            $grid->model()->whereIn('order_id', $orderIds);
        } else if (Admin::user()->isRole('sale_employee') ) {
            $customers = User::where('staff_sale_id', Admin::user()->id)->pluck('id');
            $orders = PurchaseOrder::whereIn('customer_id', $customers)->pluck('id');
            $grid->model()->whereIn('order_id', $orders);
        } else if (Admin::user()->isRole('order_manager')) {
            // $orders = PurchaseOrder::where('supporter_order_id', Admin::user()->id)->pluck('id');
            // $grid->model()->whereIn('order_id', $orders);
        }
        else if (Admin::user()->isRole('order_employee')) {
            $orders = PurchaseOrder::where('supporter_order_id', Admin::user()->id)->pluck('id');
            $grid->model()->whereIn('order_id', $orders);
        }

        $grid->filter(function($filter) use ($orderService) {
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
            $filter->column(1/4, function ($filter) use ($orderService) {
                $filter->equal('status', 'Trạng thái sản phẩm')
                    ->select(
                        PurchaseOrderItemStatus::whereIn(
                            'code', 
                            ['in_order', 'wait_order', 'vn_received', 'out_stock']
                        )
                        ->get()
                        ->pluck('name', 'id')
                    );


                
                if (! Admin::user()->isRole('customer')) {
                    $filter->where(function ($query) {
                        if ($this->input != "") {
                            $orderIds = PurchaseOrder::select('transport_code', 'id')->where('transport_code','like', '%'.$this->input.'%')->pluck('id');
                            $query->whereIn('order_id', $orderIds);
                        }
                    }, 'Mã vận đơn', 'transport_code');
                }
            });

            $filter->column(1/4, function ($filter) {
                $filter->between('order_at', 'Ngày đặt hàng')->date();

                if (! Admin::user()->isRole('customer')) {
                    $filter->like('cn_order_number', 'Mã giao dịch');
                }
            });
            $filter->column(1/4, function ($filter) {
                if (! Admin::user()->isRole('customer')) {
                    $filter->where(function ($query) {
                        if ($this->input != "all") {
                            switch ($this->input) {
                                case "all":
                                    break;
                                case "product_add":
                                    $query->where('cn_code', '!=', null);
                                    break;
                                case "product_not_add":
                                    $query->whereNull('cn_code');
                                    break;
                                case "order_not_add";
                                    $orderIds = PurchaseOrder::select('id')->whereNull('transport_code')->pluck('id');
                                    $query->whereIn('order_id', $orderIds);
                                    break;
                            }
                        }
                    }, 'Trạng thái Mã vận đơn', 'status_transport_code')->select([
                        'all'   =>  'Tất cả',
                        'product_add'   =>  'Sản phẩm đã có MVD',
                        'product_not_add'   =>  'Sản phẩm chưa có MVD',
                        'order_not_add'     =>  'Đơn hàng chưa có MVD',
                    ]);

                    $service = new UserService();
                    $options = $service->GetListOrderEmployee();
                    $options[0] = 'Chưa gán';

                    $filter->where(function ($query) {
                        if ($this->input == 0) {
                            $orderIds = PurchaseOrder::select('id')->whereNull('supporter_order_id')->pluck('id');
                            $query->whereIn('order_id', $orderIds);
                        } else {
                            $orderIds = PurchaseOrder::select('id')->where('supporter_order_id', $this->input)->pluck('id');
                            $query->whereIn('order_id', $orderIds); 
                        }
                    }, 'Nhân viên đặt hàng', 'staff_order_id')->select($options);

                }
                
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
                    'is_label'  =>  false,
                    'text'      =>  $this->getTimeline()
                ]
            ];
            return view('admin.system.core.list', compact('data'));
        });
        $grid->column('product_image', 'Ảnh')->lightbox(['width' => 40])->width(60);
        $grid->column('product_link', 'Link SP')->display(function () {
            return "<a href='$this->product_link' target='_blank'>Xem</a>";
        })->width(100);
        
        if (Admin::user()->isRole('order_manager')) {

            $grid->product_size('Kích thước')->style('text-align: right; max-width: 100px;')->editable();
            $grid->product_color('Màu')->style('text-align: right; max-width: 100px;')->editable();
        } else {

            $grid->product_size('Kích thước')->style('text-align: right; max-width: 100px;');
            $grid->product_color('Màu')->style('text-align: right; max-width: 100px;');
        }

        // trường hợp đang ở màn hình chi tiết đơn hàng mua hộ
        $flag_qty = false;
        $is_edit_qty_reality = true;
        if (strpos(url()->current(), route('admin.purchase_orders.index')) !== false) {
            $param = str_replace(route('admin.purchase_orders.index')."/", "", url()->current());
            if ($param != null) {
                $orderId = (int) $param;
                $status = PurchaseOrder::find($orderId)->status;

                if ($status == $orderService->getStatus('new-order')) {
                    $flag_qty = true;
                }

                // if ($status == $orderService->getStatus('ordered')) {
                //     $is_edit_qty_reality = false;
                // }
            }
        }

        if ($flag_qty) {
            $grid->qty('Số lượng lên đơn')->editable()->style('text-align: right; max-width: 100px;');
        } else {
            $grid->qty('Số lượng lên đơn')->style('text-align: right; max-width: 100px;');
        }
      
        if (Admin::user()->isRole('customer') || ! $is_edit_qty_reality) {
            $grid->qty_reality('Số lượng thực đặt')->style('text-align: right; max-width: 100px;');
        } else {
            $grid->qty_reality('Số lượng thực đặt')->style('text-align: right; max-width: 100px;')->editable();
        }
        
        if (Admin::user()->isRole('order_manager')) {
            $grid->price('Đơn giá')->editable();
        } else {
            $grid->price('Đơn giá')->display(function () {
                try {
                    $price_rmb = (float) $this->price;
                    $price_rmb = number_format($price_rmb, 2, '.', '');
                    $price_vnd = str_replace(",", "", $price_rmb) * $this->order->current_rate;
    
                    $data = [
                        'amount_rmb'   =>  [
                            'is_label'   =>  false,
                            'text'      =>  $price_rmb
                        ]
                    ];            
                    return view('admin.system.core.list', compact('data'));
                } catch (\Exception $e) {
                    return "<span style='color: red'> Lỗi $this->id</span>";
                }
            })->style('text-align: right; max-width: 150px;');
        }

        $grid->purchase_cn_transport_fee('VC nội địa TQ')->editable()->style('text-align: right; max-width: 150px;');
        $grid->column('total_price', 'Tổng tiền sản phẩm')->display(function () {

            try {
                $purchase_cn_transport_fee = $this->purchase_cn_transport_fee != null ? $this->purchase_cn_transport_fee : 0;
                $purchase_cn_transport_fee = (float) $purchase_cn_transport_fee;
                $purchase_cn_transport_fee = number_format($purchase_cn_transport_fee, 2, '.', '');

                $price = (float) $this->price;
                $price = number_format($price, 2, '.', '');
                $price_rmb = $this->qty_reality * $price + $purchase_cn_transport_fee ;
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
            $grid->order_cn_code('Mã vận đơn')->display(function () {

                if ($this->order->transport_code != "") {
                    $arr = explode(',', $this->order->transport_code);
                    $html = "";
                    foreach ($arr as $code) {
                        $class = 'default';
                        if (TransportCode::where('transport_code', $code)->whereIn('status', [1, 4, 5])->count() > 0) {
                            $class = 'primary';
                        } else if (TransportCode::where('transport_code', $code)->whereIn('status', [3])->count() > 0) {
                            $class = 'success';
                        }
                        $html .= "<span class='label label-$class' style='margin-bottom: 5px !important;'>$code</span> &nbsp;";
                    }
    
                    return $html;
                }

                return null;
            })->width(150);
            $grid->cn_code('MVD trên sản phẩm')->editable();
            $grid->cn_order_number('Mã giao dịch')->editable();
        }

        Admin::script($this->script());

        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->disableColumnSelector();

        if (Admin::user()->isRole('customer')) {
            $grid->disableActions();
        }

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
            $actions->disableDelete();

            if (($this->row->order == null) || ! in_array($this->row->order->status, [2, 4])) {
                $actions->disableEdit();
            }

            if (! Admin::user()->isRole('customer')) {
                $actions->append(new AddTransportCode($this->row->order_id));
            }

        });

        $grid->tools(function (Grid\Tools $tools) {

            if (! Admin::user()->isRole('customer')) {
                // if ($order->items()->whereStatus($orderService->getItemStatus('in_order'))->count() > 0) {
                    // $tools->append(new ConfirmOrderItem());
                    $tools->append(new ConfirmVnReceiveItem());
                    // $tools->append(new ConfirmOutstockItem());
                // }
            }
           
            $tools->batch(function(Grid\Tools\BatchActions $actions) {
                $actions->disableDelete();
            });
        });

        return $grid;
    }

    public function form() {
        $form = new Form(new PurchaseOrderItem());
        
        $form->currency('qty', 'Số lượng lên đơn')->digits(0)->symbol('');
        $form->text('customer_note', 'Khách hàng ghi chú');
        if (Admin::user()->isRole('customer')) {
            
        } else {
            $form->currency('qty_reality', 'Số lượng thực đặt')->digits(0)->symbol('');
            $form->text('admin_note', 'Admin ghi chú');
            $form->textarea('cn_order_number', 'Mã giao dịch');
            $form->textarea('cn_code', 'Mã vận đơn trên sản phẩm');
            $form->currency('purchase_cn_transport_fee', 'VC nội địa TQ')->digits(1)->symbol('');
            $form->text('product_size', 'Kích thước');
            $form->text('product_color', 'Màu sắc');
            $form->currency('price', 'Đơn giá')->digits(2)->symbol('');
        }

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
            $tools->disableList();
        });
        
        $form->saved(function (Form $form) {
            if ($form->model()->qty_reality == 0) {
                // het hang
                PurchaseOrderItem::find($form->model()->id)->update([
                    'status'    =>  4,
                    'outstock_at'   =>  now()
                ]);
            } else if ($form->model()->qty_reality > 0 && $form->model()->status == 4) {
                PurchaseOrderItem::find($form->model()->id)->update([
                    'status'    =>  0,
                    'outstock_at'   =>  null
                ]);
            }
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

            $('.column-qty_reality a').each(function () {
                $(this).attr('data-url', "{$route}" + "/" + $(this).attr('data-pk'));
            });

            $('.column-admin_note a').each(function () {
                $(this).attr('data-url', "{$route}" + "/" + $(this).attr('data-pk'));
            });

            $('.column-cn_order_number a').each(function () {
                $(this).attr('data-url', "{$route}" + "/" + $(this).attr('data-pk'));
            });

            $('.column-purchase_cn_transport_fee a').each(function () {
                $(this).attr('data-url', "{$route}" + "/" + $(this).attr('data-pk'));
            });

            $('.column-product_size a').each(function () {
                $(this).attr('data-url', "{$route}" + "/" + $(this).attr('data-pk'));
            });

            $('.column-product_color a').each(function () {
                $(this).attr('data-url', "{$route}" + "/" + $(this).attr('data-pk'));
            });

            $('.column-price a').each(function () {
                $(this).attr('data-url', "{$route}" + "/" + $(this).attr('data-pk'));
            });
SCRIPT;
    }

    public function showRebuild($transportCode) {
        $orderIds = PurchaseOrder::select('transport_code', 'id')->where('transport_code','like', '%'.$transportCode.'%')->pluck('id');
        $items = PurchaseOrderItem::whereIn('order_id', $orderIds)->get();

        return response()->json([
            'status'    =>  true,
            'data'      =>  $items,
            'html'      =>  view('admin.system.purchase_order.search_items', compact('items'))->render()
        ]);
    }

    public function vnReceived(Request $request) {
        PurchaseOrderItem::find($request->id)->update([
            'status'    =>  3,
            'vn_receive_at' =>  now()
        ]);

        $res = PurchaseOrderItem::find($request->id);
        $status = $res->statusText->name;
        return response()->json([
            'status'    =>  true,
            'data'  =>  PurchaseOrderItem::find($request->id),
            'status'    =>  $status
        ]);
    }
    
}