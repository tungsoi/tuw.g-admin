<?php

namespace App\Admin\Controllers\TransportOrder;

use App\Admin\Actions\Core\BtnView;
use App\Admin\Actions\Customer\Recharge;
use App\Admin\Actions\PaymentOrder\Cancel;
use App\Admin\Actions\PaymentOrder\ExportTransportCode;
use App\Admin\Services\OrderService;
use App\Admin\Services\UserService;
use App\Jobs\HandleCustomerWallet;
use App\Jobs\SubWalletWeightCustomer;
use App\Models\PaymentOrder\PaymentOrder;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\PurchaseOrder\PurchaseOrderItem;
use App\Models\System\ExchangeRate;
use App\Models\System\TransactionWeight;
use App\Models\System\Warehouse;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Illuminate\Http\Request;
use App\Models\TransportOrder\TransportCode;
use App\User;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Row;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Widgets\Box;
use App\Admin\Actions\PaymentOrder\MergeOrder;
use Encore\Admin\Widgets\Table;

class PaymentController extends AdminController
{
    protected $title = "Thanh toán";

    /**
     * Index interface.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function indexRebuild($id, Content $content)
    {
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
            .box-header{
                display: none;
            }
        ');
        
        return $content
            ->title($this->title)
            ->description($this->description['index'] ?? trans('admin.list'))
            ->row(function (Row $row) use ($id)
            {   
                $row->column(12, function (Column $column) use ($id)
                {
                    $transport_code_ids = explode(",", $id);
                    $payment_type = $_GET['type'];

                    $column->append((new Box('', $this->form($transport_code_ids, $payment_type)))); 
                });
            });
    }

    public function form($transport_code_ids, $payment_type) {
        $form = new Form(new TransportCode());

        $userService = new UserService();
        $transportCodes = TransportCode::whereIn('id', $transport_code_ids)->get();

        $form->setTitle('Nhập thông tin mã vận đơn');

        $form->column(3, function ($form) use ($payment_type) {
            $help = "";
            if ($payment_type == 'payment_not_export') {
                $help = "Thanh toán các mã vận đơn đã chọn. Trạng thái của mã vận chuyển thành chưa xuất kho. Không trừ tiền ví khách hàng.";
            } else if ($payment_type == 'payment_export') {
                $help = "Thanh toán các mã vận đơn đã chọn, tự động trừ tiền ví khách hàng. Trạng thái của mã vận chuyển thành đã xuất kho.";
            } else if ($payment_type == 'payment_temp') {
                $help = "Thanh toán tiền vận chuyển của các mã vận đơn đã chọn, cộng tiền còn thiếu của đơn hàng mua hộ. Tự động trừ tiền ví khách hàng. Trạng thái mã vận đơn chuyển thành đã xuất kho.";
            }
            $form->hidden('order_type')->default($payment_type);
            $form->select('payment_note', 'LOẠI THANH TOÁN')->options([
                'payment_temp'  =>  'Thanh toán tạm',
                'payment_export'    =>  'Thanh toán + xuất kho',
                'payment_not_export'    =>  'Thanh toán + chưa xuất kho'
            ])->default($payment_type)->disable()->help($help);
        });

        if (isset($_GET['mode']) && $_GET['mode'] != null) {
            $form->hidden('mode')->default($_GET['mode']);
        }

        $form->column(1, function ($form) {
        });

        $form->column(3, function ($form) use ($userService) {
            $form->select('payment_user_id', 'KHÁCH HÀNG THANH TOÁN')
                ->options(User::whereIsActive(User::ACTIVE)->whereIsCustomer(User::CUSTOMER)->pluck('symbol_name', 'id'))
                ->rules(['required']);

            $asset = asset("images/logo-zalo.jpeg");
            $html = "<a href='' target='_blank' id='zalo-contact'><img src=".$asset." width=20/> &nbsp;<b id='customer_phone_number'></b></a>";
            $form->html($html);
        });
        $form->column(1, function ($form) {

        });

        $form->column(4, function ($form) {
            $form->html( view('admin.system.purchase_order.customer_info_payment')->render() );
        });

        $order_id = "";
        $purchaseOrderData = null;
        if (isset($_GET['order_id']) && $_GET['order_id'] != null) {
            $order_id = $_GET['order_id'];
            $purchaseOrderData = PurchaseOrder::find($order_id);
        }

        if ($purchaseOrderData != null ) {

            $form->column(12, function ($form) use ($purchaseOrderData) {
                $form->divider('ĐƠN HÀNG MUA HỘ');
                $form->html( view('admin.system.purchase_order.payment_purchase_order_info', compact('purchaseOrderData'))->render() );
            });
        }

        $form->column(12, function ($form) use ($transportCodes) {
            $form->divider('DANH SÁCH MÃ VẬN ĐƠN');
            $form->html( view('admin.system.purchase_order.payment', compact('transportCodes'))->render() );
        });

        $form->column(3, function ($form) {
            $form->html( view('admin.system.purchase_order.internal_note')->render() );
        });

        $form->column(1, function ($form) {
        });

        $form->column(3, function ($form) {
            $form->html( view('admin.system.purchase_order.discount')->render() );
        });

        $form->column(1, function ($form) {
        });

        $form->column(4, function ($form) {
            $form->html( view('admin.system.purchase_order.wallet_weight')->render() );
        });

        $form->column(12, function ($form) use ($transportCodes, $purchaseOrderData) {
            $form->divider('CHI TIẾT THANH TOÁN');
            $amount_advance_drag = $transportCodes->sum('advance_drag');
            $current_rate = ExchangeRate::first()->vnd;
            $amount_kg = $transportCodes->sum('kg');
            $form->html( view('admin.system.purchase_order.detail_payment', compact('amount_advance_drag', 'amount_kg', 'current_rate', 'purchaseOrderData'))->render() );
        });

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
            $tools->disableList();
        });

        // $form->disableSubmit();
        $form->disableReset();

        $form->confirm('Xác nhận thanh toán ?');

        Admin::style('
            form .col-sm-2, form .col-sm-8 {
                width: 100%;
                text-align: left !important;
                padding: 0px !important;
            }

            form .input-group-addon {
                display: none;
            }

            #has-many-payment .add {
                display: none;
            }

            .box {
                border: none !important;
            }
            .input-group {
                width: 100%;
            }

            .has-many-payment-form td:nth-child(1) {
                min-width: 250px !important;
            }

            form input {
                width: 100% !important;
            }

            form td {
                heigth: 40px;
                // padding: 0px !important;
            }
        ');

        Admin::script($this->script());

        return $form;
    }

    // handle payment
    public function storeRebuild(Request $request) {
        $order_service = new OrderService();

        $arr_transport_code = $request->transport_code_id;
        
        // case merge order
        $old_payment_order_note = null;
        $old_order_ids = [];
        if (isset($request->mode) && $request->mode == 'merge_order') {
            $old_order_ids = TransportCode::whereIn('id', $arr_transport_code)->pluck('order_id')->toArray();
            $old_order_ids = array_unique($old_order_ids);
    
            $old_payment_order = PaymentOrder::whereIn('id', $old_order_ids)->pluck('order_number')->toArray();
            $old_payment_order_note = " / Gộp các đơn:  " . implode(", ", $old_payment_order);
        }

        // step 1: Tạo đơn hàng thanh toán
        $payment_order_data = [
            'order_number'  =>  $order_service->generatePaymentOrderNumber(),
            'status'        =>  $request->order_type,
            'amount'        =>  (int) $request->total_money,
            'total_kg'      =>  (float) $request->count_kg,
            'total_m3'      =>  (float) $request->count_cublic_meter,
            'total_v'       =>  (float) $request->count_volumn,
            'total_advance_drag'    => (float) $request->advan_vnd,
            'user_created_id'   =>  Admin::user()->id,
            'payment_customer_id'   => (int) $request->payment_user_id,
            'internal_note'     =>  $request->internal_note . $old_payment_order_note,
            'discount_value'    =>  (float) $request->discount_value,
            'discount_type'     =>  (int) $request->discount_type,
            'price_kg'          =>  (int) str_replace(",", "", $request->sum_kg),
            'price_m3'          =>  (int) str_replace(",", "", $request->sum_cublic_meter),
            'price_v'           =>  (int) str_replace(",", "", $request->sum_volumn),
            'is_sub_customer_wallet_weight' =>  (int) $request->wallet_weight,
            'total_sub_wallet_weight'   => $request->payment_customer_wallet_weight_used,
            'current_rate'      =>  (int) ExchangeRate::first()->vnd
        ];

        $order = PaymentOrder::firstOrCreate($payment_order_data);
        PaymentOrder::find($order->id)->update([
            'order_number'  =>  "C".$order->id
        ]);

        // case merge order
        if (isset($request->mode) && $request->mode == 'merge_order') {
            PaymentOrder::whereIn('id', $old_order_ids)->update([
                'status'    =>  'cancel',
                'internal_note' =>  "Gộp về đơn: " . "C".$order->id
            ]);
        }

        // step 2: Update danh sách mã vận đơn
        $status_update = $payment_order_data['status'] == "payment_export" 
                    ? $order_service->getTransportCodeStatus('payment')
                    : $order_service->getTransportCodeStatus('not-export');

        $transport_code_data_update = [
            'order_id'  =>  $order->id,
            'status'    =>  $status_update,
            'payment_at'    =>  now(),
            'payment_user_id'   =>  Admin::user()->id
        ];

        if ($payment_order_data['status'] == "payment_export") {
            $transport_code_data_update['export_at'] = now();
        }

        $payment_type = $request->payment_type;
        $ids = [];
        foreach ($arr_transport_code as $index => $transport_code_id) {
            $ids[] = $transport_code_id;
            $transport_code_data_update['payment_type'] = $payment_type[$index];
            TransportCode::find($transport_code_id)->update($transport_code_data_update);
        }
        
        $ware_house_id = TransportCode::whereIn('id', $ids)->whereNotNull('ware_house_id')->first()->ware_house_id;
        PaymentOrder::find($order->id)->update([
            'warehouse_id'  =>  $ware_house_id
        ]); 

        // step 3: Trừ tiền ví khách hàng với đơn hàng thanh toán xuất kho
        if ($payment_order_data['status'] == 'payment_export') {
            $job = new HandleCustomerWallet(
                $payment_order_data['payment_customer_id'],
                Admin::user()->id,
                $payment_order_data['amount'],
                3,
                "Thanh toán đơn hàng vận chuyển " . "C".$order->id,
                $order->id
            );
            dispatch($job);
        }

        //step 4: Thanh toán tiền cân, trừ luôn nếu là đơn hàng xuất kho
        if ($payment_order_data['status'] == 'payment_export' && $payment_order_data['total_sub_wallet_weight'] != 0) {
            $job_weight = new SubWalletWeightCustomer(
                $payment_order_data['payment_customer_id'],
                $payment_order_data['total_sub_wallet_weight'],
                Admin::user()->id,
                "Thanh toán đơn hàng vận chuyển " . "C".$order->id
            );
            dispatch($job_weight);
        }
        
        admin_toastr('Thanh toán thành công', 'success');

        return redirect()->route('admin.transport_codes.index', ['pci' => $payment_order_data['payment_customer_id']]);
    }

    public function script() {
        $route  = route('admin.china_receives.store');

        return <<<SCRIPT
        $( document ).ready(function() {

            // new
            $('.box-footer .btn-success').addClass('btn-md');
            $('.box-footer .btn-success').removeClass('btn-sm');
            $('.box-footer .btn-success').html('Thanh toán');

            $('.box-footer .col-md-8').prepend('<button type="button" class="btn btn-primary btn-md" style="margin-bottom: 10px; display: block;" id="btn-re-cal">Tính số liệu</button>');

            $('#select.payment_user_id').on("select2-selecting", function(e) {
                $("#search_code").select2("data",e.choice);
            });
            // old 
            $('#has-many-payment .add').click();

            $(document).bind("paste", function(e) {
                $('#scan-alert').hide();
                $('#scan-alert span').html("");

                // call ajax submit
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    url: "{$route}",
                    type: 'POST',
                    dataType: "JSON",
                    data: {
                        transport_code: e.originalEvent.clipboardData.getData('text')
                    },
                    success: function (response)
                    {
                        console.log(response);

                        if (! response.status) {
                            $('#scan-alert').show();
                            $('#scan-alert span').html(response.message);
                        } else {
                            $.admin.toastr.success(response.message, '', {timeOut: 2000});

                            $( ".has-many-payment-form" ).last().find('.transport_code').prop('disabled', true);
                            $('#has-many-payment .add').click();
                            $( '.col-md-4 tbody' ).append(
                                "<tr> <td>"+response.data.transport_code+"</td> <td>0</td> <td>0 / 0 / 0</td> <td>0</td>  </tr>"
                            );
                        }
                    }
                });


                // fail -> hien thi box error
                // success -> next row -> disable dong hien tai

                setTimeout(function () {
                    $( ".has-many-payment-form" ).last().find('.transport_code').focus();
                }, 500);
            } );
        });
SCRIPT;
    }

    public function indexAll(Content $content) {
        return $content
            ->title('Đơn hàng thanh toán')
            ->description($this->description['index'] ?? trans('admin.list'))
            ->body($this->grid());
    }

    public function grid() {
        $grid = new Grid(new PaymentOrder());

        $grid->model()
        ->where('status', '!=', 'cancel')
        ->orderBy('id', 'desc')
        ->where('payment_customer_id', '!=', 0)
        ->with('userCancel')
        ->with('transportCode')
        ->with('paymentCustomer');
        
        if (Admin::user()->isRole('customer')) {
            $grid->model()->where('payment_customer_id', Admin::user()->id);
        }

        $grid->filter(function($filter) {
            $filter->expand();
            $filter->disableIdFilter();

            $service = new UserService();
            $filter->column(1/4, function ($filter) use ($service) {
                $filter->like('order_number', 'Mã đơn hàng');

                if (! Admin::user()->isRole('customer') ) {
                    $filter->in('user_created_id', 'Người tạo')->multipleSelect($service->GetListWarehouseEmployee());
                    $filter->where(function ($query) {
                        // if ($this->input == 0) {
                            $sale_id = $this->input;
                            $customers = User::select('id')->whereIsCustomer(1)
                                ->whereIsActive(1)
                                ->where('staff_sale_id', $sale_id)
                                ->pluck('id');
                            $query->whereIn('payment_customer_id', $customers);
                        // }
                    }, 'Nhân viên Kinh doanh', 'staff_sale_id')->select($service->GetListSaleEmployee());
                }
            });

            $filter->column(1/4, function ($filter) {
                $filter->where(function ($query) {
                    if ($this->input != "") {
                        $orderIds = TransportCode::select('order_id', 'customer_code_input')->where('customer_code_input', 'like', '%'.$this->input.'%')->pluck('order_id');
                        $query->whereIn('id', $orderIds);
                    }
                }, 'Mã khách hàng', 'customer_code_input');


                $filter->equal('status', 'Trạng thái')->select([
                    'payment_export'    =>  'Thanh toán xuất kho',
                    'payment_not_export'   =>   'Thanh toán chưa xuất kho',
                    'cancel'    =>  'Huỷ'
                ]);
            });
            $filter->column(1/4, function ($filter) use ($service)  {
                if (! Admin::user()->isRole('customer') ) {
                    $filter->equal('payment_customer_id', 'Khách hàng thanh toán')->select($service->GetListCustomer());

                    $warehouse = $service->GetListWarehouse();
                    $warehouse[0] = "Chưa gán";
                    $filter->equal('warehouse_id', 'Kho hàng thanh toán')->select($warehouse);
                }
            }); 
            $filter->column(1/4, function ($filter) {
                $filter->between('created_at', 'Ngày tạo')->date();
                $filter->between('export_at', 'Ngày thanh toán')->date();

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

        if (isset($_GET['pci']) && $_GET['pci'] != "") {
            $grid->header(function () {
                $customer = User::select('id', 'symbol_name', 'wallet')->where('id', $_GET['pci'])->first();
                $url = route('admin.payments.all');
                return view('admin.system.transport_order.popup_payment_customer', compact('customer', 'url'))->render();
            });
        }

        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT');
        $grid->status('Trạng thái')->display(function () {
            $data = [
                'order_number' => [
                    'is_label'  =>  true,
                    'color' =>  'primary',
                    'text'  =>  $this->order_number
                ],  
                'amount_rmb'   =>  [
                    'is_label'   =>  true,
                    'color'     =>  $this->statusColor(),
                    'text'      =>  $this->statusText()
                ],
                'warehouse' =>  [
                    'is_label'  =>  false,
                    'text'  =>  $this->warehouse->name ?? ""
                ]
            ];

            if ($this->status == "cancel") {
                $data[] = [
                    'is_label'  =>  false,
                    'text'  =>  $this->userCancel->name ?? ""
                ];

                $data[] = [
                    'is_label'  =>  false,
                    'text'  =>  $this->cancel_at
                ];
            }
            return view('admin.system.core.list', compact('data'));
        });
        $grid->customer_input_name('Mã khách hàng')->display(function () {
            return $this->transportCode->first()->customer_code_input ?? "";
        });
        $grid->symbol_name('Khách hàng thanh toán')->display(function () {
           $html = $this->paymentCustomer->symbol_name . "<br>";
           $zalo = "https://zalo.me/" . $this->paymentCustomer->phone_number;
           $asset = asset("images/logo-zalo.jpeg");
           $html .= "<a href=".$zalo." target='_blank'><img src=".$asset." width=20/></a>";
           
           return $html;
        });
        $grid->transport_code_number('Số mã vận đơn')->display(function (){
            return $this->transportCode->count();
        })->expand(function ($model) {
            $data = [];

            if ($model->transportCode->count() > 0) {
                foreach ($model->transportCode as $key => $transportCode) {
                    $payment_type = "";
                    if ($transportCode->payment_type == 1) {
                        $payment_type = "Khối lượng";
                    } else if ($transportCode->payment_type == -1) {
                        $payment_type = "Mét khối";
                    } else {
                        $payment_type = "V/6000";
                    }
                    $data[] = [
                        $key+1,
                        $transportCode->transport_code,
                        $transportCode->customer_code_input,
                        $payment_type,
                        $transportCode->kg,
                        $transportCode->length,
                        $transportCode->width,
                        $transportCode->height,
                        $transportCode->advance_drag,
                        $transportCode->statusText->name
                    ];
                }
            }
        
            return new Table([
                    'STT',
                    'Mã vận đơn',
                    'Tên khách hàng',
                    'Loại thanh toán',
                    'KG',
                    'Dài',
                    'Rộng',
                    'Cao',
                    'Ứng kéo',
                    'Trạng thái'
                ], $data);
        })->style('max-width: 150px; text-align: center;');
        $grid->total_kg('Số KG')->display(function () {
            return str_replace('.0', '', $this->total_kg);
        })->totalRow();
        $grid->price_kg('Giá KG')->display(function () {
            return number_format($this->price_kg);
        });
        $grid->discount_type('Giảm trừ KG')->display(function () {
            if ($this->discount_type == 1) {
                return "+" . $this->discount_value;
            } else {
                return "-" . $this->discount_value;
            }
        });
        $grid->wallet_weight('Sử dụng ví cân')->display(function () {
            if ($this->is_sub_customer_wallet_weight == 1) {
                return str_replace('.0', '', number_format($this->total_sub_wallet_weight, 1, '.', ''));
                // return number_format($this->total_sub_wallet_weight, 1, '.', '');
            } else {
                return 0;
            }
        });
        $grid->total_m3('Số khối')->totalRow();
        $grid->price_m3('Giá khối')->display(function () {
            if ($this->price_m3 == "") {
                return 0;
            } else {
                return number_format($this->price_m3);
            }
            
        });
        $grid->total_v('Số V/6000')->display(function () {
            return str_replace('.00', '', $this->total_v);
        });
        $grid->price_v('Giá V/6000')->display(function () {
            return number_format($this->price_v);
        });
        $grid->amount('Tổng tiền')->display(function () {
            return number_format($this->amount);
        })->label('success')->totalRow(function ($amount) {
            return number_format($amount);
        });
        $grid->userCreated()->name('Người tạo');
        $grid->column('created_at', "Ngày tạo")->display(function () {
            return date('H:i | d-m-Y', strtotime($this->created_at));
        })->style('text-align: center');
        $grid->column('export_at', "Ngày thanh toán")->display(function () {
            $time = null;
            if ($this->status == 'payment_export') {
                $time = $this->export_at != null ? $this->export_at : $this->created_at;
            }
            
            return $time == null ? "" : date('H:i | d-m-Y', strtotime($time));
        })->style('text-align: center');
        $grid->internal_note('Ghi chú');

        $grid->disableCreateButton();
        $grid->disableExport();
        // $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->paginate(10);
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableEdit();
            $actions->disableDelete();
            $actions->disableView();


            $actions->append(new BtnView($this->row->id, route('admin.payments.showRebuild', $this->row->id)));

            if ($this->row->status == 'payment_not_export' && (Admin::user()->isRole('warehouse_employee') || Admin::user()->isRole('ar_employee') || Admin::user()->isRole('administrator'))) {
                $route = route('admin.payments.exportOrder'); // route export
                $actions->append(new ExportTransportCode($this->row->id, $route));
            }

            if (Admin::user()->isRole('warehouse_employee') || Admin::user()->isRole('ar_employee') || Admin::user()->isRole('administrator')) {
                $actions->append(new Recharge($this->row->payment_customer_id));
            }

            if (Admin::user()->isRole('warehouse_employee') || Admin::user()->isRole('administrator')) {
                $actions->append(new Cancel($this->row->id));
            }

            if ($this->row->status != "payment_not_export") {
                Admin::script(
                    <<<EOT
                    $('input[data-id={$this->row->id}]').parent().parent().empty();
EOT);
            }
        });

        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function(Grid\Tools\BatchActions $actions) {
                $actions->disableDelete();
            });
            
            if (! Admin::user()->isRole('customer')) {
                $tools->append(new MergeOrder());
            }
            
            
        });

        Admin::script(
            <<<EOT
            $('tfoot').each(function () {
                $(this).insertAfter($(this).siblings('thead'));
            });
EOT);

        return $grid;
    }

    public function exportOrder(Request $request) {
        $orderService = new OrderService();
        if ($request->ajax()) {
            $paymentOrder = PaymentOrder::find($request->order_id);
            $paymentOrder->status = 'payment_export';
            $paymentOrder->export_at = now();
            $paymentOrder->user_export_id = Admin::user()->id;
            $paymentOrder->save();

            if ($paymentOrder->transportCode->count() > 0) {
                foreach ($paymentOrder->transportCode as $transport_code) {
                    $transport_code->status  =  $orderService->getTransportCodeStatus('payment');
                    $transport_code->save();
                }
            }

            // trừ tiền khách hàng

            $job = new HandleCustomerWallet(
                $paymentOrder->payment_customer_id,
                Admin::user()->id,
                $paymentOrder->amount,
                3,
                "Thanh toán đơn hàng vận chuyển " . $paymentOrder->order_number,
                $paymentOrder->id
            );
            dispatch($job);

            if ($paymentOrder->total_sub_wallet_weight != 0) {
                $job_weight = new SubWalletWeightCustomer(
                    $paymentOrder->payment_customer_id,
                    $paymentOrder->total_sub_wallet_weight,
                    Admin::user()->id,
                    "Thanh toán đơn hàng vận chuyển " . $paymentOrder->order_number
                );
                dispatch($job_weight);
            }
            
        }

        return response()->json([
            'status'    =>  true,
            'message'   =>  'Xuất kho thành công',
            'isRedirect'    =>  true,
            'url'       =>  route('admin.payments.all', ['pci'  =>  $paymentOrder->payment_customer_id])
        ]);
    }

    public function showRebuild($id, Content $content) {
        return $content
            ->title('Chi tiết đơn thanh toán')
            ->description($this->description['show'] ?? trans('admin.show'))
            ->row(function (Row $row) {
                
                $row->column(12, function (Column $column)
                {
                    $column->append(
                        '<a class="btn btn-danger btn-sm pull-left" style="margin-bottom: 10px;" id="btn-print-this-page">In hoá đơn</a>'
                    );
                });

            })
            ->row(function (Row $row) use ($id)
            {
                $row->column(12, function (Column $column) use ($id) 
                {
                    $column->append((new Box('Thông tin đơn hàng', $this->gridDetail($id)->render())));
                });

                $paymentOrder = PaymentOrder::find($id);
                if ($paymentOrder->purchase_order_id != null && $paymentOrder->purchase_order_id != 0) {
                    $purchaseOrderData = PurchaseOrder::find($paymentOrder->purchase_order_id);
                    $row->column(12, function (Column $column) use ($id, $purchaseOrderData) 
                    {
                        $column->append((new Box('Đơn hàng mua hộ', view('admin.system.purchase_order.payment_purchase_order_info', compact('purchaseOrderData'))->render())));
                    });
                }

                $row->column(12, function (Column $column) use ($id) 
                {
                    $column->append((new Box('Mã vận đơn', $this->gridListTransportCode($id)->render())));
                });
            })
            ->row(function (Row $row) use ($id)
            {
                $row->column(6, function (Column $column) use ($id) 
                {
                    $column->append((new Box('Giảm trừ cân nặng', $this->gridDiscount($id)->render())));
                });

                $row->column(6, function (Column $column) use ($id) 
                {
                    $column->append((new Box('Sử dụng ví cân', $this->gridWalletWeight($id)->render())));
                });
            })
            ->row(function (Row $row) use ($id)
            {
                $row->column(12, function (Column $column) use ($id) 
                {
                    $column->append((new Box('Chi tiết thanh toán', $this->gridPayment($id)->render())));
                });

                $row->column(12, function (Column $column) use ($id) 
                {
                    $column->append((new Box('Ký nhận', $this->gridNotifi($id)->render())));
                });
            });
    }

    public function gridDetail($id) {
        $order = PaymentOrder::find($id);
        $headers = ['Trạng thái', 'Mã đơn hàng', 'Tổng tiền', 'Mã khách hàng', 'Khách hàng thanh toán', 'Ngày thanh toán', 'Ngày xuất kho', 'Người tạo'];
        
        $data = [
            'amount_rmb'   =>  [
                'is_label'   =>  true,
                'color'     =>  $order->statusColor(),
                'text'      =>  $order->statusText()
            ]
        ];

        $rows = [
            [
                view('admin.system.core.list', compact('data'))->render(),
                $order->order_number,
                number_format($order->amount),
                $order->transportCode->first()->customer_code_input ?? "",
                $order->paymentCustomer->symbol_name ?? "",
                date('H:i | d-m-Y', strtotime($order->created_at)),
                $order->payment_at != null ?  date('H:i | d-m-Y', strtotime($order->payment_at)) : null,
                $order->userCreated->name ?? ""
            ]
        ];

        $table = new Table($headers, $rows);

        Admin::style('
            form .col-sm-2, form .col-sm-8 {
                width: 100%;
                text-align: left !important;
                padding: 0px !important;
            }

            form .input-group-addon {
                display: none;
            }

            #has-many-payment .add {
                display: none;
            }

            .box {
                border: none !important;
            }
            .input-group {
                width: 100%;
            }

            .has-many-payment-form td:nth-child(1) {
                min-width: 250px !important;
            }

            form input {
                width: 100% !important;
            }

            table {
                text-align: right;
            }

            .td-50 th, .td-50 td {
                width: 50%;
                text-align: center !important;
            }
        ');

        return $table;
    }

    public function gridListTransportCode($id) {
        $order = PaymentOrder::find($id);
        $headers = [
            'STT',	
            'Mã vận đơn',	
            'KG',
            'Dài',
            'Rộng',
            'Cao',		
            'Thể tích',	
            'Mét khối',
            'Loại thanh toán',	
            'Ứng kéo (Tệ)',	
            'Giá dịch vụ (VND)',	
            'Tổng tiền'
        ];
        
        $rows = [];
        $total_kg = 0;
        $total_m3 = 0;
        $total_v = 0;
        $total_advance_drag = 0;
        $total_amount = 0;

        if ($order->transportCode->count() > 0 ) {
            foreach ($order->transportCode as $key => $code) {
                $price = 0;
                $amount = 0;

                if ($code->payment_type == 1) {
                    $payment_type = "Khối lượng";
                    $price = $order->price_kg;
                    $amount = $price * $code->kg;
                } else if ($code->payment_type == -1) {
                    $payment_type = "Mét khối";
                    $price = $order->price_m3;
                    $amount = $price * $code->m3_cal();
                } else {
                    $payment_type = "V/6000";
                    $price = $order->price_v;
                    $amount = $price * $code->v();
                }

                $rows[] = [
                    $key+1,
                    $code->transport_code,
                    $code->kg,
                    $code->length,
                    $code->width,
                    $code->height,
                    $code->v(),
                    $code->m3_cal(),
                    $payment_type,
                    $code->advance_drag,
                    number_format($price),
                    number_format($amount)
                ];

                $total_kg += $code->kg;
                $total_m3 += $code->m3_cal();
                $total_v += $code->v();
                $total_advance_drag += $code->advance_drag;
                $total_amount += $amount;
            }
        }

        $rows[] = [
            '',
            '',
            $total_kg,
            '',
            '',
            '',
            $total_v,
            $total_m3,
            '',
            $total_advance_drag,
            '',
            number_format($total_amount)
        ];



        $table = new Table($headers, $rows);

        return $table;
    }

    public function gridDiscount($id) {
        $order = PaymentOrder::find($id);
        $headers = ['Loại giảm trừ', 'KG'];

        $rows = [
            [
                $order->discount_type == 1 ? "Giảm đi" : "Tăng lên",
                ($order->discount_type == 1 ? "- " : "+ ") . $order->discount_value
            ]
        ];

        $table = new Table($headers, $rows, ['td-50']);

        return $table;
    }

    public function gridWalletWeight($id) {
        $order = PaymentOrder::find($id);
        $headers = ['Loại giảm trừ', 'KG'];

        $rows = [
            [
                $order->is_sub_customer_wallet_weight == 1 ? "Trừ vào ví cân" : "Không trừ ví",
                $order->total_sub_wallet_weight
            ]
        ];

        $table = new Table($headers, $rows, ['td-50']);

        return $table;
    }

    public function gridPayment($id) {
        $order = PaymentOrder::find($id);
        $headers = ['', 'Số lượng', 'Giá dịch vụ', 'Thành tiền'];

        $rows = [
            [
                '<b style="float: left !important;">Tổng cân</b> <br> <i style="float: left !important;">( Đã bao gồm giảm trừ cân nặng và sử dụng ví cân)</i>',
                $order->total_kg,
                number_format($order->price_kg),
                number_format($order->total_kg * $order->price_kg),
            ],
            [
                '<b style="float: left !important;">Tổng V/6000</b>',
                $order->total_v,
                number_format($order->price_v),
                number_format($order->total_v * $order->price_v),
            ],
            [
                '<b style="float: left !important;">Tổng khối</b>',
                $order->total_m3,
                number_format($order->price_m3),
                number_format($order->total_m3 * $order->price_m3),
            ],
            [
                '<b style="float: left !important;">Tổng ứng kéo</b>',
                '',
                '',
                number_format($order->total_advance_drag)
            ]
        ];


        if ($order->purchase_order_id != null ) {
            $rows[] = 
            [
                '<b style="float: left !important;">Tiền phải thanh toán order</b>',
                '',
                '',
                number_format($order->owed_purchase_order)
            ];
        }

        $rows[] = 
        [
            '<b style="float: left !important;">Tổng tiền</b>',
            '',
            '',
            number_format($order->amount)
        ];

        $table = new Table($headers, $rows);

        return $table;
    }

    public function gridNotifi($id) {
        $order = PaymentOrder::find($id);
        $headers = [];

        $rows = [
            [
                '<b style="width: 50%;">Yêu cầu Khách hàng kiểm tra đủ số lượng hàng hoá và đúng mã vận đơn theo đơn hàng. Nếu có sai sót xin phản ánh lại với công ty trong vòng 24h.</b>', 
                '<b>Khách hàng ký nhận</b> <br> <i> ('.date('H:i | d-m-Y', strtotime(now())).') </i> <br> <br> <br> <br> <br><b>' . ($order->paymentCustomer->symbol_name ?? "") . "</b>"
            ]
        ];

        $table = new Table($headers, $rows, ['td-50']);

        return $table;
    }

    public function cancel(Request $request) {

        if ($request->ajax()) {
            $id = $request->only(['order_id']);

            $transport_codes = TransportCode::whereOrderId($id)->get();

            $note = "";
            if ($transport_codes->count() > 0) {
                $note = $transport_codes->pluck('transport_code');

                // update trang thai don hang

                $data = [
                    'status'    =>  "cancel",
                    'user_cancel_id'    =>  Admin::user()->id,
                    'cancel_at' =>  now(),
                    'internal_note' =>  $note
                ];
                $order = PaymentOrder::where('id', $id)->first();
                $order->update($data);

                // update trang thai ma van don
                TransportCode::whereOrderId($id)->update([
                    'order_id'  =>  null,
                    'status'    =>  1,
                    'payment_at'    =>  null,
                    'payment_user_id'   =>  null,
                    'payment_type'  =>  null,
                    'export_at' =>  null,
                    'user_export_id'    =>  null
                ]);

                // update vi can
                if ($order->is_sub_customer_wallet_weight == 1 && $order->total_sub_wallet_weight > 0) {
                    $weight = number_format($order->total_sub_wallet_weight, 1, '.', '');
                    $customer = $order->paymentCustomer;
                    $customer->wallet_weight += $weight;
                    $customer->save();

                    TransactionWeight::create([
                        'customer_id'   =>  $order->payment_customer_id,
                        'user_id_created'   =>  $data['user_cancel_id'],
                        'content'   =>  "Huỷ đơn hàng thanh toán ".$order->order_number.", cộng lại ví cân cho khách",
                        'kg'    =>  $weight
                    ]);
                }
            }

            return response()->json([
                'status'    =>  true,
                'message'   =>  'Huỷ đơn thành công'
            ]);
        }
        
    }
}
