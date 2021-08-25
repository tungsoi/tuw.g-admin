<?php

namespace App\Admin\Controllers\PurchaseOrder;

use App\Admin\Services\OrderService;
use App\Admin\Services\UserService;
use App\Jobs\HandleCustomerWallet;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\PurchaseOrder\PurchaseOrderItem;
use App\Models\PurchaseOrder\PurchaseOrderStatus;
use App\Models\System\Alert;
use App\Models\System\Warehouse;
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

        // Khach hang
        if (Admin::user()->isRole('customer')) {
            $grid->model()->whereCustomerId(Admin::user()->id);
        }

        $grid->filter(function($filter) {
            $filter->expand();
            $filter->disableIdFilter();

            $service = new UserService();
            $filter->column(1/4, function ($filter) use ($service) {
                $filter->like('order_number', 'Mã đơn hàng');

                if (! Admin::user()->isRole('customer')) {
                    $filter->equal('customer_id', 'Mã khách hàng')->select($service->GetListCustomer());   
                } 

                $filter->equal('status', 'Trạng thái')->select(PurchaseOrderStatus::pluck('name', 'id'));
            });

            if (! Admin::user()->isRole('customer')) {
                $filter->column(1/4, function ($filter) use ($service) {
                    $filter->equal('supporter_id', 'Nhân viên kinh doanh')->select($service->GetListSaleEmployee());

                    $order_ids = DB::table('admin_role_users')->where('role_id', 4)->get()->pluck('user_id');
                    $filter->equal('supporter_order_id', 'Nhân viên đặt hàng')->select($service->GetListOrderEmployee());
                    
                    $filter->equal('warehouse_id', 'Kho nhận hàng')->select($service->GetListWarehouse());
                });
            }
            $filter->column(1/4, function ($filter) {
                $filter->between('created_at', 'Ngày tạo')->date();
                $filter->between('deposited_at', 'Ngày cọc')->date();
                $filter->between('order_at', 'Ngày đặt hàng');

                // if (! Admin::user()->isRole('customer')) {
                //     $filter->where(function ($query) {
                //         if ($this->input == '0') {
                //             $dayAfter = (new DateTime(now()))->modify('-7 day')->format('Y-m-d H:i:s');
                //             $query->where('deposited_at', '<=', $dayAfter)
                //         ->whereIn('status', []);
                //         }
                //     }, 'Tìm kiếm', '7days')->radio(['Đơn hàng chưa hoàn thành trong 7 ngày']);
                // }
            });
            $filter->column(1/4, function ($filter) {

                $filter->between('vn_receive_at', 'Ngày về Việt Nam');
                $filter->between('success_at', 'Ngày hoàn thành');
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

        if (Admin::user()->isRole('customer')) {
            $grid->product_image('Ảnh sản phẩm')->display(function () {
                if (! $this->items->first()) {
                    return null;
                }
                else {
                    $route = "";
    
                    if (substr( $this->items->first()->product_image, 0, 7 ) === "images/") {
                        $route = asset('storage/admin/'.$this->items->first()->product_image);
                    } else {
                        $route = $this->items->first()->product_image;
                    }
                    return '<img src="'.$route.'" style="max-width:120px;max-height:120px" class="img img-thumbnail">';
                }
            });
        }

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
                    'text'      =>  "<i>Tỷ giá: ".number_format($this->current_rate) . " (vnd) </i>"
                ],
                'total_item'    =>  [
                    'is_label'  =>  false,
                    'text'      =>  "".$this->totalItems() . " sản phẩm"
                ]
            ];
            return view('admin.system.core.list', compact('data'));
        });

        $grid->status('Trạng thái')->display(function () {
            $data = [
                'status'        =>  [
                    'is_label'  =>  true,
                    'color'     =>  $this->statusText->label,
                    'text'      =>  $this->statusText->name
                ],
                'timeline'        =>  [
                    'is_label'  =>  false,
                    'text'      =>  "<i>" . $this->getStatusTimeline() . "</i>"
                ],
                'useraction'        =>  [
                    'is_label'  =>  false,
                    'text'      =>  "<i>" . $this->getUserAction() . "</i>"
                ]
            ];
            return view('admin.system.core.list', compact('data'));
        });
        

        $grid->shop_name('Tên Shop')->style('max-width: 150px');

        if (! Admin::user()->isRole('customer')) {
            $grid->customer_id('Khách hàng')->display(function () {
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
                        'text'      =>  number_format($this->customer->wallet) . " (vnd)",
                        'route'     =>  route('admin.customers.transactions', $this->customer_id)
                    ],
                    'zalo'      =>  [
                        'is_link'   =>  true,
                        'text'      =>  $this->customer->phone_number,
                        'route'     =>  "https://zalo.me/" .  $this->customer->phone_number
                    ]
                ];
                return view('admin.system.core.list', compact('data'));
            });

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
        }

        $grid->sumItemPrice('Tổng giá sản phẩm')->display(function () {
            $price_rmb = $this->sumItemPrice();
            $price_vnd = str_replace(",", "", $this->sumItemPrice()) * $this->current_rate;
            $deposite = $price_vnd / 100 * 70;
            $data = [
                'amount_rmb'   =>  [
                    'is_label'   =>  false,
                    'text'      =>  $price_rmb . " (tệ)"
                ],
                'amount_vnd'  =>  [
                    'is_label'  =>  false,
                    'text'      =>  "<i>= ". number_format($price_vnd) . " (vnd)" ."</i>"
                ],
                'deposite'  =>  [
                    'is_label'  =>  false,
                    'text'      =>  "<i style='color: red'>Tiền cọc: ". number_format($deposite) . " (vnd)" ."</i>"
                ]
            ];            
            return view('admin.system.core.list', compact('data'));
        })->style('text-align: right');

        if (! Admin::user()->isRole('customer')) {
            $grid->purchase_order_service_fee('Phí dịch vụ')->editable()->style('text-align: right');
        } else {
            $grid->purchase_order_service_fee('Phí dịch vụ')->display(function () {
                $data = [
                    'amount_rmb'   =>  [
                        'is_label'   =>  false,
                        'text'      =>  $this->purchase_order_service_fee . " (tệ)"
                    ],
                    'amount_vnd'  =>  [
                        'is_label'  =>  false,
                        'text'      =>  "<i>= ".number_format(str_replace(",", "", $this->purchase_order_service_fee) * $this->current_rate) . " (vnd)" ."</i>"
                    ]
                ];            
                return view('admin.system.core.list', compact('data'));
            })->style('text-align: right');
        }

        $grid->sumShipFee('Phí vận chuyển')->display(function () {
            $data = [
                'amount_rmb'   =>  [
                    'is_label'   =>  false,
                    'text'      =>  $this->sumShipFee() . " (tệ)" 
                ],
                'amount_vnd'  =>  [
                    'is_label'  =>  false,
                    'text'      =>  "<i>= ".number_format(str_replace(",", "", $this->sumShipFee()) * $this->current_rate) . " (vnd)" ."</i>"
                ]
            ];            
            return view('admin.system.core.list', compact('data'));
        })->style('text-align: right');

        $grid->amount('Tổng giá cuối')->display(function () {
            $data = [
                'amount_rmb'   =>  [
                    'is_label'   =>  false,
                    'text'      =>  $this->amount(). " (tệ)"
                ],
                'amount_vnd'  =>  [
                    'is_label'  =>  false,
                    'text'      =>  "<i>= ".number_format(str_replace(",", "", $this->amount()) * $this->current_rate) . " (vnd)" ."</i>"
                ]
            ];            
            return view('admin.system.core.list', compact('data'));
        })->style('text-align: right;');

        $grid->sumItemWeight('Tổng cân')->display(function () {
            return $this->sumItemWeight();
        })->style('text-align: right');

        $grid->deposited('Đã cọc')->display(function () {
            return number_format($this->deposited) . " (vnd)";
        })->style('text-align: right;');

        if (! Admin::user()->isRole('customer')) {
            // $grid->transport_code('Mã vận đơn')->style('text-align: right; width: 150px');
            $grid->final_payment('Tổng thanh toán')->editable()->style('text-align: right');
        }
        
        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->disablePagination();
        $grid->disablePerPageSelector();
        $grid->paginate(20);
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            // if (Admin::user()->isRole('customer')) {
                $actions->disableEdit();
            // }

            $orderService = new OrderService();
            if (! in_array($this->row->status, [$orderService->getStatus('new-order'), $orderService->getStatus('deposited')]) ) {
                $actions->disableDelete();
            }

        });

        return $grid;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $service = new OrderService();
        $service->canclePurchaseOrder($id);

        admin_success('Huỷ đơn thành công');
        return redirect()->back();
    }

    /**
     * Show interface.
     *
     * @param mixed   $id
     * @param Content $content
     *
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content->header('Đơn hàng mua hộ')
        ->description('Chi tiết đơn hàng')
        ->row(function (Row $row) use ($id)
        {
            $info_width = 5;
            $mvd_width = 4;
            $update_width = 3;

            if (Admin::user()->isRole('customer')) {
                $info_width = 8;
                $mvd_width = 4;
                $update_width = 0;
            }

            $row->column($info_width, function (Column $column) use ($id) 
            {
                $column->append((new Box('Thông tin đơn hàng', $this->detail($id))));
            });

            
            $row->column($mvd_width, function (Column $column) use ($id)
            {
                $column->append((new Box('Danh sách mã vận đơn', $this->transportCode($id))));
            });

            if (! Admin::user()->isRole('customer')) {
                $row->column($update_width, function (Column $column) use ($id) {
                    $column->append((new Box('Mã vận đơn / Tệ thanh toán', $this->form($id)->edit($id))));
                });
            }

            $row->column(12, function (Column $column) use ($id)
            {
                $column->append((new Box('Danh sách sản phẩm', $this->items($id)->render())));
            });
        });
    }

    protected function detail($id) {
        $order = PurchaseOrder::find($id);
        $headers = ['Mã đơn hàng', 'Tỷ giá', 'Tên shop'];

        $item_price_rmb = $order->sumItemPrice();
        $item_price_vnd = str_replace(",", "", $order->sumItemPrice()) * $order->current_rate;
        $deposite = $item_price_vnd / 100 * 70;
        $amount_rmb = $order->amount();
        $amount_vnd = str_replace(",", "", $amount_rmb) * $order->current_rate;

        $deposited_at = $order->deposted_at != null ? date('H:i | d-m-Y', strtotime($order->deposited_at)) : "";
        $rows = [
            [
                "<b id='order_number' onclick='copyElementText(this.id)' style='cursor:pointer'>" . $order->order_number . " / " . $order->customer->symbol_name . " <a style='font-weight: 400;'> - Copy</a></b>", 
                "<span style='float: right'>". number_format($order->current_rate) . " (vnd) <br> Kho: ".$order->warehouse->name."</span>",
                "<span style='float: right'>". $order->shop_name . "</span>"
            ],
            [
                'Trạng thái',
                "<span style='float: right' class='label label-".$order->statusText->label."'>".$order->statusText->name."</span>",
                "<span style='float: right'>". $order->getStatusTimeline() . "</span>"
            ],
            [
                'Số sản phẩm',
                "<span style='float: right'>". 'Lên đơn: ' .$order->items->sum('qty'). "</span>",
                "<span style='float: right'>". 'Thực đặt: ' .$order->items->sum('qty_reality'). "</span>"
            ],
            [
                'Tổng giá sản phẩm',
                "<span style='float: right'>". $item_price_rmb . " (tệ) </span>",
                "<span style='float: right'>". number_format($item_price_vnd) . " (vnd) </span>"
            ],
            [
                'Phí dịch vụ',
                "<span style='float: right'>". $order->purchase_order_service_fee . " (tệ) </span>",
                "<span style='float: right'>". number_format(str_replace(",", "", $order->purchase_order_service_fee) * $order->current_rate) . " (vnd) </span>"
            ],
            [
                'Phí vận chuyển',
                "<span style='float: right'>". $order->sumShipFee() .  " (tệ) </span>",
                "<span style='float: right'>". number_format(str_replace(",", "", $order->sumShipFee()) * $order->current_rate) . " (vnd) </span>"
            ],
            [
                'Tổng giá cuối',
                "<b style='float: right'>". $amount_rmb . " (tệ) </b>",
                "<b style='float: right'>". number_format($amount_vnd) . " (vnd)</b> "
            ],
            [
                'Tiền cần cọc',
                "<span style='float: right'> <i> 70% tổng giá trị sp </i></span>",
                "<b style='float: right; color: blue'>". number_format($deposite) . " (vnd) </b>"
            ],
            [
                'Đã cọc',
                "<span style='float: right'> <i> ". $deposited_at ." </i></span>",
                "<b style='float: right; color: green'>". number_format($order->deposited) . " (vnd) </b>"
            ],
            [
                'Còn thiếu',
                '',
                "<b style='float: right; color: red'>". number_format($amount_vnd - $order->deposited) . " (vnd) </b>"
            ]
        ];

        $orderService = new OrderService();
        if ($order->status == $orderService->getStatus('new-order') && Admin::user()->isRole('customer')) {
            $rows[] = [
                '',
                '',
                view('admin.system.purchase_order.customer_deposite', compact('id'))->render()
            ];
        }

        $table = new Table($headers, $rows);

        return $table;
    }

    public function transportCode($id) {
        $transport_code_str = PurchaseOrder::find($id)->transport_code;
        $headers = ['Mã vận đơn', 'Khối lượng', 'Giá', 'Tổng tiền'];
        $rows = [];

        $total_kg = 0;
        $total_price = 0;
        if ($transport_code_str != "") {
            $transport_code_arr = explode(",", $transport_code_str);
            
            if (sizeof($transport_code_arr) > 0) {
                foreach ($transport_code_arr as $key => $code_row) {
                    $code = TransportCode::select('transport_code', 'kg', 'price_service')->where('transport_code', $code_row)->first();

                    if ($code) {
                        $amount = $code->kg * $code->price_service;
                        $total_kg += $code->kg;
                        $total_price += $amount;
    
                        $rows[] = [
                            $code->transport_code,
                            "<span style='float: right'>".$code->kg."</span>",
                            "<span style='float: right'>".number_format($code->price_service)."</span>",
                            "<span style='float: right'>".number_format($amount, 0)."</span>"
                        ];
                    } else {
                        $rows[] = [
                            $code_row,
                            'Chưa có dữ liệu',
                            '',
                            ''
                        ];
                    }
                    
                }

                $rows[] = [
                    '',
                    "<span style='float: right'>".$total_kg."</span>",
                    '',
                    "<span style='float: right'>".number_format($total_price, 0)."</span>"
                ];
            } else {
                $rows[] = [
                    'Mã vận đơn sai',
                    '',
                    '',
                    ''
                ];
            }
        } else {
            $rows[] = [
                'Trống',
                '',
                '',
                ''
            ];
        }


        $table = new Table($headers, $rows);

        return $table;
    }

    public function items($orderId) {
        $itemController = new PurchaseOrderItemController();
        $grid = $itemController->grid($orderId);

        $grid->model()->whereOrderId($orderId)->orderBy('id', 'desc');

        $grid->disableTools();
        $grid->disableBatchActions();
        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->disableFilter();
        $grid->disableActions();
        $grid->paginate(30);
        $grid->disablePagination();
        $grid->disablePerPageSelector();

        return $grid;
    }

    public function form() {
        $form = new Form(new PurchaseOrder());
        $form->setTitle('TIỀN THANH TOÁN');

        $form->hidden('id');
        $form->tags('transport_code', "Mã vận đơn")->help('Mã đầu tiên được hiểu là MVD chính, các mã sau là MVD phụ.');
        $form->currency('final_payment', "Tệ thanh toán")->symbol('Tệ')->digits(2)->style('width', '100%');

        $form->currency('offer_cn', 'Chiết khẩu')->symbol('Tệ')->digits(2)->readonly()->style('width', '100%');
        $form->currency('offer_vn', 'Chiết khẩu')->symbol('VND')->digits(0)->readonly()->style('width', '100%');

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
            $tools->disableList();
        });

        Admin::style('
            form .col-sm-2, form .col-sm-8 {
                width: 100%;
                text-align: left !important;
                padding: 0px !important;
            }
            .box {
                border: none !important;
            }
        ');

        Admin::script($this->offerOrderScript());

        $form->saved(function (Form $form) {
            $id = $form->model()->id;
            $order = PurchaseOrder::find($id);
            $amount = $order->amount();

            $order->offer_cn = number_format($amount - $order->final_payment, 2);
            $order->offer_vn = number_format(($amount - $order->final_payment) * $order->current_rate, 0);
            $order->save();

            admin_toastr('Chỉnh sửa thành công', 'success');
            return redirect()->back();
        });

        return $form;
    }

    public function offerOrderScript() {
        return <<<SCRIPT
            $('form').attr('action', location.href);
            $('form').prev().remove();
            $('form button[type="reset"]').remove();
SCRIPT;
    }

    // Khách hàng tự đặt cọc đơn hàng
    public function customerDeposite(Request $request) {
        if ($request->ajax()) {
            $data = $request->only(['id']);

            $order = PurchaseOrder::find($data['id']);
            $deposite = $order->depositeAmountCal();
            $customer_wallet = $order->customer->wallet;

            if ($deposite > $customer_wallet) {
                // tiền cọc > tiền còn dư
                return response()->json([
                    'status'    =>  false,
                    'message'   =>  'Số dư tài khoản không đủ. Vui lòng liên hệ nhân viên CSKH để nạp tiền.'   
                ]);
            } else {
                // tiền cọc < tiền còn dư
                // đủ điều kiện đặt cọc

                $orderService = new OrderService();
                $order->status = $orderService->getStatus('deposited');
                $order->deposited = $deposite;
                $order->deposited_at = now();
                $order->user_deposited_at = Admin::user()->id;
                $order->save();

                $job = new HandleCustomerWallet(
                    Admin::user()->id,
                    1, // system
                    $deposite,
                    3,
                    "Đặt cọc đơn hàng mua hộ $order->order_number"
                );
                dispatch($job);

                return response()->json([
                    'status'    =>  true,
                    'message'   =>  "Đặt cọc đơn hàng mua hộ $order->order_number thành công."
                ]);
            }
        }
    }
    
}