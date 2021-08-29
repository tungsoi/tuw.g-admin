<?php

namespace App\Admin\Controllers\TransportOrder;

use App\Admin\Services\UserService;
use App\Models\System\Warehouse;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Illuminate\Http\Request;
use App\Models\TransportOrder\TransportCode;
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
                $help = "Thanh toán các mã vận đơn đã chọn, tự động trừ tiền ví khách hàng. Trạng thái của mã vận chuyển thành chưa xuất kho";
            }
            $form->select('payment_type', 'LOẠI THANH TOÁN')->options([
                'payment_temp'  =>  'Thanh toán tạm',
                'payment_export'    =>  'Thanh toán + xuất kho',
                'payment_not_export'    =>  'Thanh toán + chưa xuất kho'
            ])->default($payment_type)->disable()->help($help);
        });

        $form->column(1, function ($form) {
        });

        $form->column(3, function ($form) use ($userService) {
            $form->select('payment_user_id', 'KHÁCH HÀNG THANH TOÁN')
                ->options($userService->GetListCustomer())->rules(['required']);
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

        $form->column(12, function ($form) {
            $form->divider('CHI TIẾT THANH TOÁN');
            $form->html( view('admin.system.purchase_order.detail_payment')->render() );
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
        if ($request->ajax()) {
            $transport_code = $request->transport_code;

            if ($transport_code != "") {
                if (TransportCode::whereTransportCode($transport_code)->first()) {
                    return response()->json([
                        'status'    =>  false,
                        'message'   =>  'Mã vận đơn đã tồn tại'
                    ]);
                } else {
                    $res = TransportCode::create([
                        'transport_code'    =>  trim($transport_code),
                        'status'            =>  TransportCode::CHINA_RECEIVE,
                        'china_receive_at'  =>  now(),
                        'china_receive_user_id' =>  Admin::user()->id
                    ]);
    
                    return response()->json([
                        'status'    =>  true,
                        'message'   =>  'Lưu thành công',
                        'data'      =>  $res
                    ]);
                }
            } else {
                return response()->json([
                    'status'    =>  false,
                    'message'   =>  'Mã vận đơn không được để trống'
                ]);
            }
            
        }
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
}
