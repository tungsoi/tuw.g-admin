<?php

namespace App\Admin\Controllers\TransportOrder;

use App\Admin\Services\OrderService;
use App\Admin\Services\UserService;
use App\Models\PurchaseOrder\PurchaseOrder;
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

class VietnamReceiveController extends AdminController
{
    protected $title = "Bắn hàng Hà nội nhận";

    /**
     * Index interface.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function indexRebuild(Content $content)
    {
        return $content
            ->title($this->title)
            ->description($this->description['index'] ?? trans('admin.list'))
            ->row(function (Row $row)
            {   
                $row->column(8, function (Column $column)
                {
                    $column->append((new Box('', $this->form()))); 
                });

                $row->column(4, function (Column $column)
                {
                    $column->append((new Box('Danh sách mã đã bắn', $this->grid()))); 
                });
            });
    }

    public function info() {
        $headers = [];
        $rows = [
            [
                'Người thực hiện',
                Admin::user()->name
            ]
        ];

        $table = new Table($headers, $rows);

        return $table;
    }

    protected function grid()
    {
        $headers = ['Mã vận đơn', 'Cân', 'Kích thước', 'Ứng kéo', 'Trạng thái'];
        $rows = [];

        $table = new Table($headers, $rows);

        return $table;
    }

    // public function storeTransportCode(Request $request)
    // {
    //     TransportCode::create([
    //         'transport_code'    =>  $request->transport_code,
    //         'status'            =>  TransportCode::CHINA_RECEIVE,
    //         'china_recevie_at'  =>  now(),
    //         'china_receive_user_id' =>  Admin::user()->id,
    //         'ware_house_id' =>  Warehouse::whereIsDefault(1)->first()->id
    //     ]);

    //     return response()->json([
    //         'code'  =>  200,
    //         'data'  =>  $request->all()
    //     ]);
    // }

    public function form() {
        $form = new Form(new TransportCode());
        $form->setAction(route('admin.vietnam_receives.store'));

        $form->setTitle('Nhập thông tin mã vận đơn');

        $form->column(12, function ($form) {
            $form->html('<h4>Người thực hiện: ' . Admin::user()->name . "</h4>");
            $form->html(view('admin.system.transport_order.alert'));
        });

        $form->column(5, function ($form) {
            $form->text('customer_code_input', 'Mã khách hàng vận đơn')->rules(['required']);
        });
        $form->column(2, function ($form) {
            // $form->text('customer_code_input', 'Mã khách hàng vận đơn')->rules(['required']);
        });

        $form->column(5, function ($form) {
            $userService = new UserService();
            $form->select('ware_house_id', 'Kho hàng')->options($userService->GetListWarehouse())->default(2)->rules(['required']);
        });

        $form->column(12, function ($form) {
            $form->table('vietnam-receive', '', function ($table) {
                $table->text('transport_code', 'Mã vận đơn')->autofocus();
                $table->currency('kg', 'Cân nặng (kg)')->digits(1)->default(0);
                $table->currency('length', 'Dài (cm)')->digits(0)->default(0);
                $table->currency('width', 'Rộng (cm)')->digits(0)->default(0);
                $table->currency('height', 'Cao (cm)')->digits(0)->default(0);
                $table->currency('advance_drag', 'Ứng kéo (cm)')->digits(1)->default(0);
                $table->text('internal_note', 'Ghi chú');
            });
        });

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
            $tools->disableList();
        });

        $form->disableReset();

        $form->confirm('Lưu dữ liệu ?');

        Admin::style('
            form .col-sm-2, form .col-sm-8 {
                width: 100%;
                text-align: left !important;
                padding: 0px !important;
            }

            #has-many-vietnam-receive .input-group-addon {
                display: none;
            }

            #has-many-vietnam-receive .add {
                display: none;
            }

            .box {
                border: none !important;
            }
            .input-group {
                width: 100%;
            }

            .has-many-vietnam-receive-form td:nth-child(1) {
                min-width: 250px !important;
            }

            form input {
                width: 100% !important;
            }

            form td {
                heigth: 40px;
                padding: 0px !important;
            }

            form table input {
                border: none !important;
            }

            form table input:focus {
                background: wheat !important;
            }
        ');

        Admin::script($this->script());

        return $form;
    }

    public function storeRebuild(Request $request) {

        $data = $request->all();
        if ($request->customer_code_input == "") {
            admin_toastr("Mã khách hàng để trống", 'error');
            return redirect()->back()->withInput();        
        }

        if (sizeof($data['vietnam-receive']) > 0) {
            foreach ($data['vietnam-receive'] as $key => $code) {
                if ($code['transport_code'] != "") {

                    $code['customer_code_input']    = $data['customer_code_input'];
                    $code['ware_house_id']    = $data['ware_house_id'];
                    $code['vietnam_receive_at'] = now();
    
                    $orderService = new OrderService();
                    $code['status'] = $orderService->getTransportCodeStatus('vietnam-rev');
                    $code['vietnam_receive_user_id'] = Admin::user()->id;
                    $code['internal_note']  = json_encode(PurchaseOrder::where('transport_code', 'like', '%'.$code['transport_code'].'%')->pluck('order_number')) ?? "";
    
                    $flag = TransportCode::whereTransportCode(trim($code['transport_code']))->first();
    
                    if ($flag) {
                        // update
                        $flag->update($code);
                        $flag->save();
                    } else {
                        TransportCode::create($code);
                        // create
                    }

                    // chuyen trang thai da ve viet nam cua don hang mua ho
                    PurchaseOrder::where('transport_code', 'like', '%'.$code['transport_code'].'%')->update([
                        'status'                =>  $orderService->getStatus('vn-recevice'),
                        'vn_receive_at'         =>  now(),
                        'user_vn_receive_at'    =>  Admin::user()->id
                    ]);

                }
            }

            admin_toastr("Lưu thành công", 'success');

            return redirect()->route('admin.transport_codes.index', ['customer_code_input' => $data['customer_code_input']]);
        }

        admin_toastr("Xảy ra lỗi", 'error');
        return redirect()->back()->withInput();
    }

    public function script() {
        return <<<SCRIPT
        $( document ).ready(function() {
            $('#customer_code_input').prop('required',true);
            $('#customer_code_input').attr('oninvalid', "this.setCustomValidity('Vui lòng nhập đầy đủ tên khách hàng')");
            $('#customer_code_input').attr('oninput', "setCustomValidity('')");

            $('#has-many-vietnam-receive .add').click();

            $(document).on('keydown','.has-many-vietnam-receive-form input', function(e) {
                if (e.which == 13) 
                {
                    e.preventDefault();
                
                    // add data vao list bang da ban
                    $( '.col-md-4 tbody' ).append(
                        "<tr><td>"
                        + $( ".has-many-vietnam-receive-form" ).last().find('.transport_code').val()
                        + "</td> <td>"
                        + $( ".has-many-vietnam-receive-form" ).last().find('.kg').val()
                        + "</td> <td>"
                        + $( ".has-many-vietnam-receive-form" ).last().find('.length').val()
                        + " / "
                        + $( ".has-many-vietnam-receive-form" ).last().find('.width').val()
                        + " / "
                        + $( ".has-many-vietnam-receive-form" ).last().find('.height').val()
                        + "</td> <td>"
                        + $( ".has-many-vietnam-receive-form" ).last().find('.advance_drag').val()
                        + "</td> <td>"
                        + $( ".has-many-vietnam-receive-form" ).last().find('.internal_note').val()
                        + "</td>  </tr>"
                    );


                    // them dong moi + focus

                    $('#has-many-vietnam-receive .add').click();
                    $( ".has-many-vietnam-receive-form" ).last().find('.transport_code').focus();
                }
            });

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
                    url: "vietnam_receives/search/" + e.originalEvent.clipboardData.getData('text'),
                    type: 'GET',
                    dataType: "JSON",
                    success: function (response)
                    {
                        console.log(response);

                        if (! response.status) {
                            $.admin.toastr.error(response.message, '', {timeOut: 2000});
                            $( ".has-many-vietnam-receive-form" ).last().find('.kg').focus();
                            $( ".has-many-vietnam-receive-form" ).last().find('.kg').click();
                        } else {

                            // da co thong tin, fill vao bang
                            $( ".has-many-vietnam-receive-form" ).last().find('.kg').val(response.data.kg);
                            $( ".has-many-vietnam-receive-form" ).last().find('.length').val(response.data.length);
                            $( ".has-many-vietnam-receive-form" ).last().find('.width').val(response.data.width);
                            $( ".has-many-vietnam-receive-form" ).last().find('.height').val(response.data.height);
                            $( ".has-many-vietnam-receive-form" ).last().find('.advance_drag').val(response.data.advance_drag);
                            $( ".has-many-vietnam-receive-form" ).last().find('.internal_note').val("Da ban TQ nhan");
                            $( ".has-many-vietnam-receive-form" ).last().find('.kg').focus();
                            $( ".has-many-vietnam-receive-form" ).last().find('.kg').click();
                        }
                    }
                });
            } );
        });
SCRIPT;
    }

    public function search($code) { 
        $orderService = new OrderService();
        $transport_code = TransportCode::whereTransportCode($code)->whereStatus($orderService->getTransportCodeStatus('china-rev'))->first();

        if ($transport_code) {
            return response()->json([
                'status'    =>  true,
                'message'   =>  'Đã lấy dữ liệu',
                'data'      =>  $transport_code
            ]); 
        } else {
            return response()->json([
                'status'    =>  false,
                'message'   =>  'Mã vận đơn chưa bắn Trung Quốc nhận'
            ]);
        }
    }
}
