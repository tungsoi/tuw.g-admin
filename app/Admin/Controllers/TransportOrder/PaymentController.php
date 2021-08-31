<?php

namespace App\Admin\Controllers\TransportOrder;

use App\Admin\Services\OrderService;
use App\Admin\Services\UserService;
use App\Jobs\HandleCustomerWallet;
use App\Models\PaymentOrder\PaymentOrder;
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
                $help = "Thanh toán các mã vận đơn đã chọn, tự động trừ tiền ví khách hàng. Trạng thái của mã vận chuyển thành chưa xuất kho.";
            } else if ($payment_type == 'payment_export') {
                $help = "Thanh toán các mã vận đơn đã chọn, tự động trừ tiền ví khách hàng. Trạng thái của mã vận chuyển thành đã xuất kho.";
            }
            $form->hidden('order_type')->default($payment_type);
            $form->select('payment_note', 'LOẠI THANH TOÁN')->options([
                'payment_temp'  =>  'Thanh toán tạm',
                'payment_export'    =>  'Thanh toán + xuất kho',
                'payment_not_export'    =>  'Thanh toán + chưa xuất kho'
            ])->default($payment_type)->disable()->help($help);
        });

        $form->column(1, function ($form) {
        });

        $form->column(3, function ($form) use ($userService) {
            $form->select('payment_user_id', 'KHÁCH HÀNG THANH TOÁN')
                ->options(User::whereIsActive(User::ACTIVE)->whereIsCustomer(User::CUSTOMER)->where('wallet_weight', '>', 0)->pluck('symbol_name', 'id'))
                ->rules(['required']);
        });
        $form->column(1, function ($form) {

        });

        $form->column(4, function ($form) {
            $form->html( view('admin.system.purchase_order.customer_info_payment')->render() );
        });

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

        $form->column(12, function ($form) use ($transportCodes) {
            $form->divider('CHI TIẾT THANH TOÁN');
            $amount_advance_drag = $transportCodes->sum('advance_drag');
            $current_rate = ExchangeRate::first()->vnd;
            $amount_kg = $transportCodes->sum('kg');
            $form->html( view('admin.system.purchase_order.detail_payment', compact('amount_advance_drag', 'amount_kg', 'current_rate'))->render() );
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

    public function storeRebuild(Request $request) {
        $orderService = new OrderService();

        $listTransportCode = TransportCode::whereIn('id', $request->transport_code_id)->pluck('transport_code')->toArray();
        $content = "Thanh toán tổ hợp mã vận đơn " . (sizeof($listTransportCode) > 0 ? "(".implode(", ", $listTransportCode).")" : null);

        // step 1: create payment order
        $paymentOrderData = [
            'order_number'  =>  $orderService->generatePaymentOrderNumber(),
            'status'        =>  $request->order_type,
            'amount'        =>  $request->total_money,
            'total_kg'      =>  $request->count_kg,
            'total_m3'      =>  $request->count_cublic_meter,
            'total_v'       =>  $request->count_volumn,
            'total_advance_drag'    =>  $request->advan_vnd,
            'user_created_id'   =>  Admin::user()->id,
            'payment_customer_id'   =>  $request->payment_user_id,
            'internal_note'     =>  $request->internal_note,
            'discount_value'    =>  $request->discount_value,
            'discount_type'     =>  $request->discount_type,
            'price_kg'          =>  str_replace(",", "", $request->sum_kg),
            'price_m3'          =>  str_replace(",", "", $request->sum_cublic_meter),
            'price_v'           =>  str_replace(",", "", $request->sum_volumn),
            'is_sub_customer_wallet_weight' =>  $request->wallet_weight,
            'total_sub_wallet_weight'   =>  $request->payment_customer_wallet_weight_used,
            'current_rate'      =>  ExchangeRate::first()->vnd,
            'transaction_note'  =>  $content
        ];

        $paymentOrder = PaymentOrder::firstOrCreate($paymentOrderData);

        // step 2: update transport code
        foreach ($request->transport_code_id as $index => $transport_code_id) {
            $status = "";
            if ($request->order_type == 'payment_not_export') {
                $status = $orderService->getTransportCodeStatus('not-export');
            } else if ($request->order_type == 'payment_export') {
                $status = $orderService->getTransportCodeStatus('payment');
            }
            // else if () payment_temp
            // else if () payment_export
            TransportCode::find($transport_code_id)->update([
                'order_id'  =>  $paymentOrder->id,
                'status'    =>  $status,
                'payment_at'    =>  now(),
                'payment_user_id'   =>  Admin::user()->id,
                'payment_type'  =>  $request->payment_type[$index]
            ]);
        }

        // step 3: create transaction to wallet user
        $job = new HandleCustomerWallet(
            $request->payment_user_id,
            Admin::user()->id,
            $request->total_money,
            3,
            $content
        );
        dispatch($job);

        //step 4: update customer wallet weight and create transaction weight
        if ($request->wallet_weight == 1 && $request->payment_customer_wallet_weight_used > 0) {
            $customer = User::find($request->payment_user_id);
            $customer->wallet_weight -= $request->payment_customer_wallet_weight_used;
            $customer->save();
    
            TransactionWeight::create([
                'customer_id'   => (int) $request->payment_user_id,
                'user_id_created'   =>  Admin::user()->id,
                'content'   =>  $content,
                'kg'    =>  $request->payment_customer_wallet_weight_used
            ]);
        }

        admin_toastr('Thanh toán thành công', 'success');

        return redirect()->route('admin.transport_codes.index'); // return ve chi tiet don thanh toan
    }

    public function script() {
        $route  = route('admin.china_receives.store');

        return <<<SCRIPT
        $( document ).ready(function() {

            // new
            $('.box-footer .btn-success').addClass('btn-md');
            $('.box-footer .btn-success').removeClass('btn-sm');
            $('.box-footer .btn-success').html('Thanh toán');

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

        $grid->model()->orderBy('id', 'desc');

        if (Admin::user()->isRole('customer')) {
            $grid->model()->where('customer_payment_id', Admin::user()->id);
        }

        $grid->filter(function($filter) {
            $filter->expand();
            $filter->disableIdFilter();

            $service = new UserService();
            $filter->column(1/4, function ($filter) use ($service) {
                $filter->like('order_number', 'Mã đơn hàng');

                if (! Admin::user()->isRole('customer') ) {
                    $filter->equal('user_created_id', 'Người tạo')->select($service->GetAllEmployee());
                }
            });

            $filter->column(1/4, function ($filter) {
                $filter->like('customer_code_input', 'Mã khách hàng');

            });
            $filter->column(1/4, function ($filter) use ($service)  {
                if (! Admin::user()->isRole('customer') ) {
                    $filter->equal('customer_payment_id', 'Khách hàng thanh toán')->select($service->GetListCustomer());
                }
            }); 
            $filter->column(1/4, function ($filter) {
                $filter->between('created_at', 'Ngày thanh toán')->date();

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
        $grid->order_number('Mã đơn hàng');
        $grid->status('Trạng thái')->display(function () {
            $data = [
                'amount_rmb'   =>  [
                    'is_label'   =>  true,
                    'color'     =>  $this->statusColor(),
                    'text'      =>  $this->statusText()
                ]
            ];
            return view('admin.system.core.list', compact('data'));
        });
        $grid->customer_input_name('Mã khách hàng')->display(function () {
            return $this->transportCode->first()->customer_code_input ?? "";
        });
        $grid->paymentCustomer()->symbol_name('Khách hàng thanh toán');
        $grid->transport_code_number('Số mã vận đơn')->display(function (){
            return $this->transportCode->count();
        })->expand(function ($model) {
            $data = [];

            if ($model->transportCode->count() > 0) {
                foreach ($model->transportCode as $transportCode) {
                    $payment_type = "";
                    if ($transportCode->payment_type == 1) {
                        $payment_type = "Khối lượng";
                    } else if ($transportCode->payment_type == -1) {
                        $payment_type = "Mét khối";
                    } else {
                        $payment_type = "V/6000";
                    }
                    $data[] = [
                        $transportCode->transport_code,
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
                    'Mã vận đơn',
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
        });
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
                return str_replace('.0', '', $this->total_sub_wallet_weight);
            } else {
                return 0;
            }
        });
        $grid->total_m3('Số khối');
        $grid->price_m3('Giá khối')->display(function () {
            return number_format($this->price_m3);
        });
        $grid->total_v('Số V/6000')->display(function () {
            return str_replace('.00', '', $this->total_v);
        });
        $grid->price_v('Giá V/6000')->display(function () {
            return number_format($this->price_v);
        });
        $grid->amount('Tổng tiền')->display(function () {
            return number_format($this->amount);
        })->label('success');
        $grid->userCreated()->name('Người tạo');
        $grid->column('created_at', "Ngày tạo")->display(function () {
            return date('H:i | d-m-Y', strtotime($this->created_at));
        })->style('text-align: center');
        $grid->inernal_note('Ghi chú');

        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->paginate(100);
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableEdit();
            $actions->disableDelete();
        });

        return $grid;
    }
}
