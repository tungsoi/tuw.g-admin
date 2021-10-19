<?php

namespace App\Admin\Controllers\PurchaseOrder;

use App\Admin\Actions\Customer\Recharge;
use App\Admin\Actions\PurchaseOrder\ConfirmOrderItem;
use App\Admin\Actions\PurchaseOrder\ConfirmOutstockItem;
use App\Admin\Actions\PurchaseOrder\ConfirmVnReceiveItem;
use App\Admin\Actions\PurchaseOrder\DeleteMultipleNewOrder;
use App\Admin\Actions\PurchaseOrder\Deposite;
use App\Admin\Actions\PurchaseOrder\DepositeMultiple;
use App\Admin\Actions\PurchaseOrder\Update;
use App\Admin\Services\OrderService;
use App\Admin\Services\UserService;
use App\Jobs\HandleAdminDepositeMultiplePurchaseOrder;
use App\Jobs\HandleCustomerWallet;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\PurchaseOrder\PurchaseOrderItem;
use App\Models\PurchaseOrder\PurchaseOrderStatus;
use App\Models\System\Alert;
use App\Models\System\TeamSale;
use App\Models\System\Warehouse;
use App\Models\TransportOrder\TransportCode;
use App\User;
use Carbon\Carbon;
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
use Illuminate\Support\Facades\URL;

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
        } else if (Admin::user()->isRole('sale_employee')) {

            // check is leader team

            if (Admin::user()->isRole('sale_manager')) {
                // all
            } else {
                $flag = TeamSale::whereLeader(Admin::user()->id)->first();
                if ($flag) {
                    // is leader
                    $customers = User::whereIn('staff_sale_id', $flag->members)->pluck('id');
                    $grid->model()->whereIn('customer_id', $customers);
                } else {
                    $customers = User::where('staff_sale_id', Admin::user()->id)->pluck('id');
                    $grid->model()->whereIn('customer_id', $customers);
                }
            }
           
            
        } else if (Admin::user()->isRole('order_manager')) {
            // $grid->model()->where('supporter_order_id', Admin::user()->id);
        } else if (Admin::user()->isRole('order_employee')) {
            $grid->model()->where('supporter_order_id', Admin::user()->id);
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
                    $filter->equal('supporter_sale_id', 'Nhân viên kinh doanh')->select($service->GetListSaleEmployee());
                    $options = $service->GetListOrderEmployee();
                    $options[0] = 'Chưa gán';

                    $filter->where(function ($query) {
                        if ($this->input == 0) {
                            $query->whereNull('supporter_order_id');
                        } else {
                            $query->where('supporter_order_id', $this->input);
                        }
                    }, 'Nhân viên đặt hàng', 'supporter_order_id')->select($options);
                    
                    $filter->equal('warehouse_id', 'Kho nhận hàng')->select($service->GetListWarehouse());
                });
            }
            
            $filter->column(1/4, function ($filter) {
                $filter->between('created_at', 'Ngày tạo')->datetime();
                $filter->between('deposited_at', 'Ngày cọc')->datetime();
                $filter->between('order_at', 'Ngày đặt hàng')->datetime();
            });

            $filter->column(1/4, function ($filter) {
                $filter->between('vn_receive_at', 'Ngày về Việt Nam')->datetime();
                $filter->between('success_at', 'Ngày hoàn thành')->datetime();
                $filter->equal('order_type', 'Loại đơn hàng')->select([
                    "Taobao-1688"   =>  "Taobao-1688",
                    "Pindoudou"     =>  "Pindoudou",
                    "Wechat"        =>  "Wechat"
                ]);
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

        $grid->header(function () {
            return view('admin.system.purchase_order.header')->render();
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

            if (! Admin::user()->isRole('customer')) {
                $data[] = [
                    'is_label'  =>  true,
                    'color'     =>  'default',
                    'text'      =>  $this->customer->symbol_name
                ];
                $data[] = [
                    'is_label'  =>  false,
                    'color'     =>  'info',
                    'is_link'   =>  true,
                    'text'      =>  "Số dư: ".number_format($this->customer->wallet),
                    'route'     =>  route('admin.customers.transactions', $this->customer_id)."?mode=recharge"
                ];
            }

            return view('admin.system.core.list', compact('data'));
        })->style('max-width: 200px');
        

        if (! Admin::user()->isRole('customer')) {

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
                    'text'      =>  $price_rmb
                ],
                'amount_vnd'  =>  [
                    'is_label'  =>  false,
                    'text'      =>  "<i>= ". number_format($price_vnd) ."</i>"
                ],
                'deposite'  =>  [
                    'is_label'  =>  false,
                    'text'      =>  "<i style='color: red'>Cần cọc: <span class='default-deposite'>". number_format($deposite) . "</span></i>"
                ]
            ];            
            return view('admin.system.core.list', compact('data'));
        })->style('text-align: right');

        if (! Admin::user()->isRole('customer')) {
            $grid->purchase_order_service_fee('Phí dịch vụ')->editable()->style('text-align: right');
        } else {
            $grid->purchase_order_service_fee('Phí dịch vụ')->display(function () {
                if ($this->purchase_order_service_fee == "") {
                    $purchase_order_service_fee = 0;
                } else {
                    $purchase_order_service_fee = $this->purchase_order_service_fee;
                }
                $data = [
                    'amount_rmb'   =>  [
                        'is_label'   =>  false,
                        'text'      =>  $purchase_order_service_fee
                    ],
                    'amount_vnd'  =>  [
                        'is_label'  =>  false,
                        'text'      =>  "<i>= ".number_format(str_replace(",", "", $purchase_order_service_fee) * $this->current_rate) . "</i>"
                    ]
                ];            
                return view('admin.system.core.list', compact('data'));
            })->style('text-align: right');
        }

        $grid->sumShipFee('VC nội địa TQ')->display(function () {
            $data = [
                'amount_rmb'   =>  [
                    'is_label'   =>  false,
                    'text'      =>  $this->sumShipFee()
                ],
                'amount_vnd'  =>  [
                    'is_label'  =>  false,
                    'text'      =>  "<i>= ".number_format(str_replace(",", "", $this->sumShipFee()) * $this->current_rate) . "</i>"
                ]
            ];            
            return view('admin.system.core.list', compact('data'));
        })->style('text-align: right');

        $grid->amount('Tổng giá cuối')->display(function () {
            $data = [
                'amount_rmb'   =>  [
                    'is_label'   =>  false,
                    'text'       =>  "<b class='default-amount'>". $this->amount() . "</b>"
                ],
                'amount_vnd'  =>  [
                    'is_label'  =>  false,
                    'text'      =>  "<i>= ".number_format(str_replace(",", "", $this->amount()) * $this->current_rate) . "</i>"
                ]
            ];            
            return view('admin.system.core.list', compact('data'));
        })->style('text-align: right;');

        $grid->sumItemWeight('Tổng cân')->display(function () {
            return $this->sumItemWeight();
        })->style('text-align: right');

        $grid->deposited('Đã cọc')->display(function () {
            
            $deposited_at = "";
            if ($this->deposited_at != "") {
                $deposited_at = date('H:i | d-m-Y', strtotime($this->deposited_at));
            }
            $data = [
                'amount_rmb'   =>  [
                    'is_label'   =>  false,
                    'text'      =>   number_format($this->deposited)
                ]
            ];            
            return view('admin.system.core.list', compact('data'));

        })->style('text-align: right;');

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

        if (! Admin::user()->isRole('customer') && Admin::user()->isRole('order_employee')) {
            $grid->final_payment('Tổng thanh toán')->editable()->style('text-align: right')->width(80);
        }

        $grid->customer_note('Khách hàng ghi chú')->editable()->style('max-width: 100px');
        $grid->admin_note('Admin ghi chú')->editable()->style('max-width: 100px');

        if (! Admin::user()->isRole('customer')) {
            $grid->internal_note('Ghi chú nội bộ')->editable()->style('max-width: 100px');
        }

        if (! Admin::user()->isRole('customer')) {
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
        }
        
        $grid->disableCreateButton();
        $grid->disableExport();

        // if (! Admin::user()->can('deposite_multiple_purchase_order')) {
            // $grid->disableBatchActions();
        // }

        $grid->disableColumnSelector();
        $grid->paginate(10);
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            // if (Admin::user()->isRole('customer')) {
                $actions->disableEdit();
            // }

            $orderService = new OrderService();
            if (Admin::user()->isRole('customer')) {
                if (! in_array($this->row->status, [$orderService->getStatus('new-order')]) ) {
                    $actions->disableDelete();
                }
            }

            if (! Admin::user()->isRole('customer')) {
                if ($this->row->status == $orderService->getStatus('new-order')) {
                    $actions->append(new Deposite($this->row->id));
                }

                $actions->append(new Update($this->row->id));
                $actions->append(new Recharge($this->row->customer_id));
            }    

            if (Admin::user()->isRole('customer') && ! in_array($this->row->status, [$orderService->getStatus('new-order')])) {
                Admin::script(
                    <<<EOT
                    $('input[data-id={$this->row->id}]').parent().parent().empty();
EOT);
            }
        });
        
        $grid->tools(function (Grid\Tools $tools) {
            if (Admin::user()->can('deposite_multiple_purchase_order')) {
                $tools->append(new DepositeMultiple());
            }

            if (Admin::user()->isRole('ar_employee') || Admin::user()->isRole('administrator')) {
                $tools->append(new DeleteMultipleNewOrder());
            }
        });

        Admin::script($this->scriptGrid());

        return $grid;
    }

    public function scriptGrid() {
        return <<<SCRIPT
        console.log('grid js');

        $("input.grid-row-checkbox").on("ifChanged", function () {

            let total_deposite = 0;
            let total_amount = 0;
            var key = $.admin.grid.selected();
        
            if (key.length !== 0) {
                var i;
                for (i = 0; i < key.length; ++i) {
                    let data_key = key[i];

                    let tr_ele = $('tr[data-key="'+data_key+'"]');
                    let default_deposite = tr_ele.find('.default-deposite').html();
                    default_deposite = default_deposite.replace(/,/g, "");
                    default_deposite = parseInt(default_deposite);

                    let default_amount = tr_ele.find('.default-amount').html();
                    default_amount = default_amount.replace(/,/g, "");
                    default_amount = parseFloat(default_amount);

                    total_deposite += default_deposite;
                    total_amount += default_amount;
                }
            }

            let total_deposite_formated = number_format(total_deposite);
            let total_amount_formated = number_format(total_amount, 2);

            $('#estimate-deposited').html(total_deposite_formated);
            $('#estimate-amount-rmb').html(total_amount_formated);
            
            $('input#estimate-deposited').val(total_deposite_formated + " VND");
        });

        function number_format(number, decimals, dec_point, thousands_sep) {
            // Strip all characters but numerical ones.
            number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
            var n = !isFinite(+number) ? 0 : +number,
                prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
                dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
                s = '',
                toFixedFix = function (n, prec) {
                    var k = Math.pow(10, prec);
                    return '' + Math.round(n * k) / k;
                };
            // Fix for IE parseFloat(0.55).toFixed(0) = 0;
            s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            }
            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }
            return s.join(dec);
        }

        
 
SCRIPT;
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

            $order = PurchaseOrder::find($id);
            if (Admin::user()->isRole('customer')) {
                $info_width = 8;
                $mvd_width = 4;
                $update_width = 0;
            }


            if (! Admin::user()->isRole('customer') && $order->status != 11) {
                $row->column(12, function (Column $column) use ($id, $order) 
                {
                    $flag = false;
                    if ($order->status == 9) {
                        // thanh cong
                        if (Admin::user()->isRole('ar_employee') || Admin::user()->isRole('administrator')) {
                            $flag = true;
                        }
                    } else {
                        $flag = true;
                    }
                    
                    if ($flag && (Admin::user()->isRole('ar_employee') || Admin::user()->isRole('administrator') || Admin::user()->isRole('order_employee'))) {
                        $column->append((new Box('Thao tác', $this->action($id))));
                    }
                });   
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

            $orderService = new OrderService();
            // $flagItem = $order->items->whereNotIn('status', [$orderService->getItemStatus('out_stock'), $orderService->getItemStatus('wait_order')])->count();
            // // flagitem -> check khong con san pham nao co trang thai # het hang hoac da dat hang


            // if ($flagItem == 0 && ! Admin::user()->isRole('customer') && $order->status == $orderService->getStatus('deposited')) {
            //     $row->column(12, function (Column $column) use ($id)
            //     {
            //         $column->append((new Box('', $this->notifiOrdered($id))));
            //     });
            // }
            
            $row->column(12, function (Column $column) use ($id)
            {
                $column->append((new Box('Danh sách sản phẩm', $this->items($id)->render())));
            });
        });
    }

    protected function detail($id) {
        $order = PurchaseOrder::find($id);

        $headers = ['Mã đơn hàng', 'Tỷ giá', 'Tên shop'];

        if ($order == null) {
            $table = new Table($headers, []);
            return $table;
        }

        $item_price_rmb = $order->sumItemPrice();
        $item_price_vnd = str_replace(",", "", $order->sumItemPrice()) * $order->current_rate;
        $deposite = $item_price_vnd / 100 * 70;
        $amount_rmb = $order->amount();
        $amount_vnd = str_replace(",", "", $amount_rmb) * $order->current_rate;

        $deposited_at = $order->deposted_at != null ? date('H:i | d-m-Y', strtotime($order->deposited_at)) : "";
        $rows = [
            [
                "<b id='order_number' onclick='copyElementText(this.id)' style='cursor:pointer'>" . $order->order_number . " / " . $order->customer->symbol_name . "</b>", 
                "<span style='float: right'>". number_format($order->current_rate) . " (vnd) </span>",
                "<span style='float: right'>". $order->shop_name . "</span>"
            ],
            [
                "<span style='float: left'>Kho: ". ($order->warehouse ? $order->warehouse->name : null) . "</span>",
                "<span style='float: right'>NVKD: " .  ($order->customer->saleEmployee ? $order->customer->saleEmployee->name : null). "</span>",
                "<span style='float: right'>NVDH: " . ($order->orderEmployee ? $order->orderEmployee->name : null) ."</span>",
            ],
            [
                'Trạng thái',
                "<span style='float: right' class='label label-".$order->statusText->label."'>".$order->statusText->name. $order->countItemFollowStatus()."</span>",
                "<span style='float: right'>". $order->getStatusTimeline() . "</span>"
            ],
            [
                'Số sản phẩm',
                "<span style='float: right'>". 'Lên đơn: ' .$order->items->sum('qty'). "</span>",
                "<span style='float: right'>". 'Thực đặt: ' .$order->items->where('status', '!=', 4)->sum('qty_reality'). "</span>"
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
                'VC nội địa TQ',
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
        $headers = ['Mã vận đơn', 'Cân', 'Dài / Rộng / Cao',  'Trạng thái']; //'Giá', 'Tổng tiền',
        $rows = [];

        $total_kg = 0;
        $total_price = 0;
        if ($transport_code_str != "") {
            $transport_code_arr = explode(",", $transport_code_str);
            
            if (sizeof($transport_code_arr) > 0) {
                foreach ($transport_code_arr as $key => $code_row) {
                    if ($code_row != "") {
                        $code = TransportCode::where('transport_code', $code_row)->first();

                        if ($code) {
                            $amount = $code->kg * $code->price_service;
                            $total_kg += $code->kg;
                            $total_price += $amount;
    
                            $tag = "<a style='color: green !important;' target='_blank' href=".route('admin.transport_codes.index')."?transport_code=".$code->transport_code.">".$code->transport_code."</a>";
                            $rows[] = [
                            "<span style='float: left; color: green !important;'>".$tag."</span>",
                            "<span style='float: right; color: green;'>".$code->kg."</span>",
                            "<span style='float: right; color: green;'>".$code->length." / ".$code->width." / ".$code->height."</span>",
                            // "<span style='float: right; color: green;'>".number_format($code->price_service)."</span>",
                            // "<span style='float: right; color: green;'>".number_format($amount, 0)."</span>",
                            "<span style='float: right; color: green;'>".$code->getStatus()."</span>"
                        ];
                        } else {
                            $rows[] = [
                            $code_row,
                            'Chưa có dữ liệu',
                            '',
                            // '',
                            // '',
                            ''
                        ];
                        }
                    }
                    
                }

                $rows[] = [
                    '',
                    "<span style='float: right'>".$total_kg."</span>",
                    '',
                    // '',
                    // "<span style='float: right'>".number_format($total_price, 0)."</span>",
                    ''
                ];
            } else {
                $rows[] = [
                    'Mã vận đơn sai',
                    '',
                    '',
                    // '',
                    // '',
                    ''
                ];
            }
        } else {
            $rows[] = [
                'Trống',
                '',
                '',
                // '',
                // '',
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

        $grid->tools(function (Grid\Tools $tools) use ($orderId) {
            $order = PurchaseOrder::find($orderId);

            if (! Admin::user()->isRole('customer')) {
                $orderService = new OrderService();
                // if ($order->items()->whereStatus($orderService->getItemStatus('in_order'))->count() > 0) {
                    $tools->append(new ConfirmOrderItem());
                    // $tools->append(new ConfirmVnReceiveItem());
                    $tools->append(new ConfirmOutstockItem());
                // }
            }
           
            $tools->batch(function(Grid\Tools\BatchActions $actions) {
                $actions->disableDelete();
            });
        });

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

        if (Admin::user()->isRole('order_employee') || Admin::user()->isRole('administrator')) {
            $form->currency('final_payment', "Tệ thanh toán")->symbol('Tệ')->digits(2)->style('width', '100%');

            $form->currency('offer_cn', 'Chiết khẩu')->symbol('Tệ')->digits(2)->readonly()->style('width', '100%');
            $form->currency('offer_vn', 'Chiết khẩu')->symbol('VND')->digits(0)->readonly()->style('width', '100%');
        }

        $form->text('customer_note', 'Khách hàng ghi chú');
        $form->text('admin_note', 'Admin ghi chú');
        $form->text('internal_note', 'Ghi chú nội bộ');

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
            .box-footer .col-md-2 {
                display: none;
            }
            .box-footer .col-md-8 {
                padding-left: 0px !important;
            }
            .box-footer {
                padding-top: 0px;
                padding-bottom: 0px;
                border: none;
            }
            .col-md* {
                padding: 0px !important;
            }
        ');

        Admin::script($this->offerOrderScript());
            
        $form->saved(function (Form $form) {
            $id = $form->model()->id;
            $order = PurchaseOrder::find($id);

            $price_rmb = str_replace(",", "", $order->sumItemPrice());
            $ship = $order->sumShipFee();

            $amount = $price_rmb + $ship;

            if ($order->final_payment != "") {
                $amount = str_replace(",", "", $amount);
                $final_payment = str_replace(",", "", $order->final_payment);
                
                $order->offer_cn = number_format($amount - $final_payment, 2);
                $order->offer_vn = number_format(($amount - $final_payment) * $order->current_rate, 0);
                $order->save();
            }

            $req = request()->all();

            if (! isset($req['_editable'])) {
                admin_toastr('Chỉnh sửa thành công', 'success');
                return redirect()->back();
            }
            
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
                    Admin::user()->id, // khach hang
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

    public function adminDeposite($id, Content $content) {
        return $content
            ->title($this->title())
            ->description($this->description['show'] ?? trans('admin.show'))
            ->row(function (Row $row) use ($id)
            {
                $row->column(8, function (Column $column) use ($id) 
                {
                    $column->append((new Box('Thông tin đơn hàng', $this->detail($id))));
                });

                $row->column(4, function (Column $column) use ($id) 
                {
                    $column->append((new Box('', $this->formAdminDeposite($id))));
                });
            });
    }

    public function formAdminDeposite($id) {
        $order = PurchaseOrder::find($id);

        $form = new Form(new PurchaseOrder());
        $form->setTitle('Đặt cọc đơn hàng');

        $form->hidden('order_id')->default($id);

        $form->setAction(route('admin.purchase_orders.post_admin_deposite'));

        $form->html($order->customer->symbol_name, 'Mã khách hàng: ');

        $route = route('admin.customers.transactions', $order->customer->id);
        $link = "<a href=".$route." target='_blank'> Xem lịch sử ví</a>";
        $form->html(number_format($order->customer->wallet) . " (vnd) - " . $link, 'Số dư ví:');
        $form->html(
            '<b style="color: red">'.number_format($order->depositeAmountCal()) . " (vnd) </b>", 
            'Số tiền phải cọc tối thiểu:');

        $form->currency('deposited', 'Tiền vào cọc')->symbol('VND')->digits(0)->rules(['required'])->style('width', '100%')->default($order->depositeAmountCal());

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
            $tools->disableList();
        });

        $form->confirm('Xác nhận đặt cọc đơn hàng ?');

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

        $orderService = new OrderService();
        if ($order->status != $orderService->getStatus('new-order')) {
            $form->disableSubmit();
            $form->disableReset();

            $form->html(
                '<a href="'.route('admin.purchase_orders.index').'" class="btn btn-sm btn-danger" title="Danh sách"><i class="fa fa-arrow-left"></i><span class="hidden-xs">&nbsp;Quay lại danh sách</span></a>'
            );
        }

        return $form;
    }

    public function postAdminDeposite(Request $request) 
    {
        $orderService = new OrderService();

        $order = PurchaseOrder::find($request->order_id);

        $order->status = $orderService->getStatus('deposited');
        $order->deposited = $request->deposited;
        $order->deposited_at = now();
        $order->user_deposited_at = Admin::user()->id;
        $order->save();

        $job = new HandleCustomerWallet(
            $order->customer->id,
            Admin::user()->id, // admin
            $request->deposited,
            3,
            "Đặt cọc đơn hàng mua hộ $order->order_number"
        );
        dispatch($job);

        admin_toastr('Đặt cọc thành công', 'success');

        return redirect()->route('admin.purchase_orders.show', $order->id);
    }

    public function notifiOrdered($id) {
        return view('admin.system.purchase_order.confirm_ordered', compact('id'))->render();
    }

    // chot trang thai don hang
    public function postConfirmOrdered(Request $request) {
        if ($request->ajax()) {
            $orderService = new OrderService();

            $order = PurchaseOrder::find($request->id);
            $order->status = $orderService->getStatus($request->type);

            if ($request->type == "ordered") {
                $order->order_at = now();
                $order->user_order_at = Admin::user()->id;
            } else if ($request->type == "vn-recevice") {
                $order->vn_receive_at = now();
                $order->user_vn_receive_at = Admin::user()->id;
            } else if ($request->type == "success") {
                $order->success_at = now();
                $order->user_success_at = Admin::user()->id;

                $deposited = $order->deposited;
                $amount_rmb = $order->amount();
                $amount_vnd = str_replace(",", "", $amount_rmb) * $order->current_rate;
                $owed = $amount_vnd-$deposited;

                if ($owed > 0) {
                    $type = 3;
                    $content = "Thanh toán đơn hàng mua hộ. Mã đơn hàng ".$order->order_number;
                } else {
                    $type = 2;
                    $content = "Thanh toán đơn hàng mua hộ. Mã đơn hàng ".$order->order_number.". ( Dư tiền cọc).";
                    $owed = abs($owed);
                }

                $job = new HandleCustomerWallet(
                    $order->customer_id,
                    1,
                    $owed,
                    $type,
                    $content
                );
                dispatch($job);
            }
            
            $order->save();

            return response()->json([
                'status'    =>  true,
                'message'   =>  "Lưu thành công"
            ]);
        }
    }

    public function editData($id, Content $content) {
        return $content
            ->title($this->title())
            ->description($this->description['edit'] ?? trans('admin.edit'))
            ->body($this->formEditPortal($id));
    }

    public function formEditPortal($id) {
        $form = new Form(new PurchaseOrder());

        $userService = new UserService();

        $order = PurchaseOrder::find($id);
        $form->setAction(route('admin.purchase_orders.store_edit_data'));

        $form->column(1/2, function ($form) use ($userService, $id, $order) {

            $form->hidden('order_id')->default($order->id);
            $form->text('order_number', 'Mã đơn hàng')->readonly()->default($order->order_number);
            $form->select('customer_id', 'Mã khách hàng')->options($userService->GetListCustomer())->readonly()->default($order->customer_id);
            $form->divider();
            $form->text('shop_name', 'Tên shop')->default($order->shop_name);
            $form->text('customer_note', 'Khách hàng ghi chú')->default($order->customer_note);
            $form->text('admin_note', 'Admin ghi chú')->default($order->admin_note);
            $form->text('internal_note', 'Nội bộ ghi chú')->default($order->internal_note);
        });
        
        $form->column(1/2, function ($form) use ($order, $userService) {
            $form->html('Tổng tiền sản phẩm: ' . $order->sumItemPrice() . ' (Tệ)');
            $form->currency('purchase_order_service_fee', 'Phí dịch vụ')->symbol('Tệ')->digits(2)->default($order->purchase_order_service_fee);

            $form->select('supporter_sale_id', 'Nhân viên kinh doanh')->options($userService->GetListSaleEmployee())->default($order->supporter_sale_id);
            $form->select('supporter_order_id', 'Nhân viên đặt hàng')->options($userService->GetListOrderEmployee())->default($order->supporter_order_id);
            $form->select('warehouse_id', 'Kho hàng')->options($userService->GetListWarehouse())->default($order->warehouse_id);
            $form->select('order_type', 'Loại đơn hàng')->options([
                "Taobao-1688"   =>  "Taobao-1688",
                "Pindoudou"     =>  "Pindoudou",
                "Wechat"        =>  "Wechat"
            ])->default($order->order_type);
        });

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
            $tools->disableList();
        });

        return $form;
    }

    public function postEditData(Request $request) {
        $order = PurchaseOrder::find($request->order_id);
        $res = $order->update($request->all());

        admin_toastr('Chỉnh sửa thành công', 'success');

        return redirect()->back();
    }

    public function action($id) {
        $order = PurchaseOrder::find($id);
        $transport_codes = explode(',', $order->transport_code);
        
        // lấy danh sách các code đã về việt nam, đủ điều kiện thanh toán
        $codes = TransportCode::whereIn('transport_code', $transport_codes)->where('status', 1)->get()->pluck('id')->toArray();
        $ids_route = implode(',', $codes);
        $total = sizeof($transport_codes) ?? 0;
        $can_payment = sizeof($codes) ?? 0;
        $remain = $total - $can_payment;

        $tips = "";

        $flag_payment = true; // van chuyen + mua ho
        if (! is_array($transport_codes) && sizeof($transport_codes) == 0) {
            $flag_payment = false; // van chuyen
        } else {
            foreach ($transport_codes as $code) {
                $rs = TransportCode::where('transport_code', $code)->first();
    
                if (! $rs) {
                    $flag_payment = false;
                }
            }
        }

        if (! $flag_payment) {
            // còn mã chưa về --> chỉ thanh toán vận chuyển
            $tips = "vận chuyển " . $can_payment . " MVD";
            $payment_route = route('admin.payments.index', ['ids' => $ids_route]) . "?type=payment_export"; // <---- thanh toán xuất kho

        } else {
            // đã về hết --> thanh toán cả vận chuyển và mua hộ
            $tips = "vận chuyển ".$can_payment." VND + Mua hộ còn nợ";
            $payment_route = route('admin.payments.index', ['ids' => $ids_route]) . "?type=payment_temp&order_id=".$id;
        }

        if ($ids_route == "") {
            $payment_route = "#";
        }

        $status = $order->status;

        return view('admin.system.purchase_order.action_portal', compact('id', 'payment_route', 'total', 'can_payment', 'tips', 'status'))->render();
    }

    public function updateTransportCode(Request $request) {
        PurchaseOrder::find($request->id)->update([
            'transport_code'  =>  $request->transport_code
        ]);

        admin_toastr('Cập nhật thành công', 'success');

        return back();
    }

    public function postAdminDepositeMultiple(Request $request) {
        $data = $request->only(['percent', 'ids']);

        $ids = explode(",", $data['ids']);
        $order_numbers = PurchaseOrder::whereIn('id', $ids)->pluck('order_number')->toArray();

        foreach ($ids as $order_id) {
            $job = new HandleAdminDepositeMultiplePurchaseOrder(
                $order_id,
                $data['percent'],
                true,
                Admin::user()->id
            );
            dispatch($job);
        }

        return response()->json([
            'status'    =>  true,
            'message'   =>  "Đang xử lý đặt cọc các đơn hàng: " . implode(', ', $order_numbers) .". Vui lòng kiểm tra lại sau khoảng ". sizeof ($ids) . " phút."
        ]);
    }

    public function postCustomerDepositeMultiple(Request $request) {
        $data = $request->only(['percent', 'ids']);

        $ids = explode(",", $data['ids']);

        $orders = PurchaseOrder::whereIn('id', $ids)->where('status', 2)->get();

        $total_deposited = 0;
        foreach ($orders as $order) {
            $itemPrice = $order->sumItemPrice(false);
            $deposite = $itemPrice / 100 * 70;
            $deposite_vnd = number_format($deposite * $order->current_rate, 0, '.', '');

            $total_deposited += $deposite_vnd;
        }

        $user_wallet = (int) Admin::user()->wallet;

        if ($user_wallet < 0) {
            return response()->json([
                'status'    =>  false,
                'message'   =>  "Số dư ví của bạn không đủ để đặt cọc. Vui lòng nạp thêm vào tài khoản."
            ]);
        } else if ($user_wallet < $total_deposited) {
            return response()->json([
                'status'    =>  false,
                'message'   =>  "Số dư ví của bạn không đủ để đặt cọc. Vui lòng nạp thêm vào tài khoản."
            ]);
        } else {
            foreach ($orders as $order) {
                $job = new HandleAdminDepositeMultiplePurchaseOrder(
                    $order->id,
                    $data['percent'],
                    false,
                    Admin::user()->id
                );
                dispatch($job);
            }
    
            return response()->json([
                'status'    =>  true,
                'message'   =>  "Đã yêu cầu đặt cọc các đơn hàng vửa chọn thành công. Vui lòng kiểm tra lại sau khoảng 5 phút."
            ]);
        }
    }
    
    public function getListCustomerNewOrder() {
        $res = PurchaseOrder::select('customer_id', DB::raw('count(*) as total'))->whereStatus(2)->groupBy('customer_id')->with('customer');

        if (Admin::user()->isRole('order_employee') && ! Admin::user()->isRole('order_manager')) {
            $customers = $res->where('supporter_order_id', Admin::user()->id)->get();
        } else if (Admin::user()->isRole('sale_employee')) {
            $customers = $res->where('supporter_sale_id', Admin::user()->id)->get();
        } else {
            $customers = $res->get();
        }
        $status = 2;
        $title = "Danh sách khách hàng có đơn hàng mới (".$customers->sum('total')." đơn)";
        $html = view('admin.system.purchase_order.customer_has_new_order', compact('customers', 'status', 'title'))->render();
        return response()->json([
            'status'        =>  true,
            'message'       =>  $customers,
            'html'      =>  $html
        ]);
    }

    
    public function getListCustomerDeposittingOrder() {
        $res = PurchaseOrder::select('customer_id', DB::raw('count(*) as total'))->whereStatus(4)->groupBy('customer_id')->with('customer');
        
        if (Admin::user()->isRole('order_employee') && ! Admin::user()->isRole('order_manager')) {
            $customers = $res->where('supporter_order_id', Admin::user()->id)->get();
        } else if (Admin::user()->isRole('sale_employee')) {
            $customers = $res->where('supporter_sale_id', Admin::user()->id)->get();
        } else {
            $customers = $res->get();
        }

        $status = 4;
        $title = "Danh sách khách hàng có đơn hàng đã cọc - đang đặt (".$customers->sum('total')." đơn)";
        $html = view('admin.system.purchase_order.customer_has_new_order', compact('customers', 'status', 'title'))->render();
        return response()->json([
            'status'        =>  true,
            'message'       =>  $customers,
            'html'      =>  $html
        ]);
    }

    public function getAdminDepositeMultiple($ids, Content $content) {

        return $content
        ->title("Đặt cọc đơn hàng mua hộ")
        ->description($this->description['create'] ?? trans('admin.create'))
        ->body($this->formAdminDepositeMultiple($ids));
    }

    public function formAdminDepositeMultiple($id) {
        $form = new Form(new PurchaseOrder);

        $ids = explode(",", $id);
        $orders = PurchaseOrder::whereIn('id', $ids)->get();

        $form->setTitle('Đặt cọc đơn hàng mua hộ');
        $form->setAction(route('admin.purchase_orders.submit_admin_deposite_multiple'));
        $form->html(view('admin.system.purchase_order.admin_deposite_multiple', compact('orders'))->render());

        $form->confirm('Xác nhận đặt cọc ?');
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

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
            $tools->disableList();
        });

        return $form;
    }

    public function submitAdminDepositeMultiple(Request $request) {
        $data = $request->all();
        $ids = $request->id;
        $depositeds = $request->deposited;

        foreach ($ids as $key => $order_id) {
            $order = PurchaseOrder::find($order_id);
            $money = str_replace(',', '', $depositeds[$key]);
            $order->update([
                'deposited' => $money,
                'status'    =>  4,
                'deposited_at'  =>  now(),
                'user_deposited_at' =>  Admin::user()->id
            ]);

            $job = new HandleCustomerWallet(
                $order->customer->id,
                Admin::user()->id, // admin
                $money,
                3,
                "Đặt cọc đơn hàng mua hộ $order->order_number"
            );
            dispatch($job);
        }

        admin_toastr('Đặt cọc thành công', 'success');
        return redirect()->route('admin.transactions.index');
    }
}