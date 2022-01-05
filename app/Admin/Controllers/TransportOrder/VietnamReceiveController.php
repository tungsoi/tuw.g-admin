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
                $row->column(12, function (Column $column)
                {
                    $column->append((new Box('', $this->form()))); 
                });
                
                $row->column(11, function (Column $column)
                {
                    $column->append((new Box('Sản phẩm Order', $this->gridOrder()))); 
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
            $form->html(view('admin.system.transport_order.alert'));
        });

        $form->column(3, function ($form) {
            $form->text('customer_code_input', 'Mã khách hàng vận đơn')->rules(['required'])->attribute(['list' => 'cars', 'autocomplete'   =>  'off']);

            $names = TransportCode::select('customer_code_input')->groupBy('customer_code_input')->get();
            $html = "";
            foreach ($names as $name) {
                $html .= "<option>".$name->customer_code_input."</option>";
            }
            $form->html(
                '<datalist id="cars" style="height: 300px;">'.$html.'</datalist>'
            );
        });
        $form->column(1, function ($form) {
            // $form->text('customer_code_input', 'Mã khách hàng vận đơn')->rules(['required']);
        });

        $form->column(3, function ($form) {
            $userService = new UserService();
            $warehouses = Warehouse::all();

            $default_warehouse = 2;
            foreach ($warehouses as $warehouse) {
                if (in_array(Admin::user()->id, $warehouse->employees)) {
                    $default_warehouse = $warehouse->id;
                }
            }
            
            $form->select('ware_house_id', 'Kho hàng')
            ->options($userService->GetListWarehouse())
            ->default($default_warehouse)
            ->readonly();
        });

        $form->column(12, function ($form) {
            $form->table('vietnam-receive', '', function ($table) {
                $table->text('STT')->default(1)->readonly();
                $table->text('transport_code', 'Mã vận đơn')->autofocus();
                $table->currency('kg', 'Cân nặng (kg)')->digits(1)->default(0);
                $table->currency('length', 'Dài (cm)')->digits(0)->default(0);
                $table->currency('width', 'Rộng (cm)')->digits(0)->default(0);
                $table->currency('height', 'Cao (cm)')->digits(0)->default(0);
                $table->currency('advance_drag', 'Ứng kéo (cm)')->digits(1)->default(0);
                $table->text('internal_note', 'Ghi chú');
                $table->currency('m3', 'M3')->digits(3)->default(0);
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

            .has-many-vietnam-receive-form td:nth-child(2) {
                min-width: 250px !important;
            }
            .has-many-vietnam-receive-form td:nth-child(1) {
                width: 20px !important;
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
                    $code['status'] = 1;
                    $code['vietnam_receive_user_id'] = Admin::user()->id;
                    $code['admin_note'] = $code['internal_note'];
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
                    PurchaseOrder::where('transport_code', 'like', '%'.$code['transport_code'].'%')
                    ->where('status', '!=', 9)
                    ->update([
                        'status'                =>  7,
                        'vn_receive_at'         =>  now(),
                        'user_vn_receive_at'    =>  Admin::user()->id
                    ]);

                }
            }

            admin_toastr("Lưu thành công", 'success');

            return redirect()->route('admin.vietnam_receives.index', ['mode'    =>  'reload']);
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

            function countElement(item,array) {
                var count = 0;
                $.each(array, function(i,v) { if (v === item) count++; });
                return count;
            }

            $(document).on('keydown','.has-many-vietnam-receive-form input', function(e) {
                if (e.which == 13) 
                {
                    e.preventDefault();

                    if (e.originalEvent.target.className != "form-control vietnam-receive transport_code") 
                    {
                        $('#has-many-vietnam-receive .add').click();
                        $( ".has-many-vietnam-receive-form" ).last().find('.transport_code').focus();
                        $( ".has-many-vietnam-receive-form" ).last().find('.STT').val( $('tr.has-many-vietnam-receive-form').length );
                    } else {

                        let value = $( ".has-many-vietnam-receive-form" ).last().find('.transport_code').val();

                        let temp = [];
                        let flag = true;
                        $('.transport_code').each(function() {
                            let ele_value = $(this).val().trim();

                            let count = countElement(ele_value, temp);

                            console.log(count, "count");
                            if (count == 1) {
                                $.admin.toastr.error('MVD "'+ value +'" trùng.', '', {timeOut: 5000});
                                $( ".has-many-vietnam-receive-form" ).last().find('.transport_code').val("");
                                flag = false;
                            } else {
                                temp.push(ele_value);
                            }
                        });

                        console.log(temp, 'temp');

                        if (value != "" && flag) {
                            
                            $.ajaxSetup({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                }
                            });

                            $.ajax({
                                url: "search_items/" + value,
                                type: 'GET',
                                dataType: "JSON",
                                success: function (response)
                                {
                                    if (response.status && response.html != "") {
                                        $(".content > .row > .col-md-11 table tbody").prepend(response.html); 
                                    }
                                }
                            });

                            $.ajax({
                                url: "vietnam_receives/search/" + value,
                                type: 'GET',
                                dataType: "JSON",
                                success: function (response)
                                {
                                    console.log(response);

                                    if (! response.status) {
                                        // $.admin.toastr.warning(response.message, '', {timeOut: 2000});
                                        $( ".has-many-vietnam-receive-form" ).last().find('.kg').focus();
                                        $( ".has-many-vietnam-receive-form" ).last().find('.kg').click();
                                    } else {

                                        // da co thong tin, fill vao bang
                                        $( ".has-many-vietnam-receive-form" ).last().find('.kg').val(response.data.kg);
                                        $( ".has-many-vietnam-receive-form" ).last().find('.length').val(response.data.length);
                                        $( ".has-many-vietnam-receive-form" ).last().find('.width').val(response.data.width);
                                        $( ".has-many-vietnam-receive-form" ).last().find('.height').val(response.data.height);
                                        $( ".has-many-vietnam-receive-form" ).last().find('.advance_drag').val(response.data.advance_drag);
                                        $( ".has-many-vietnam-receive-form" ).last().find('.internal_note').val("");
                                        $( ".has-many-vietnam-receive-form" ).last().find('.kg').focus();
                                        $( ".has-many-vietnam-receive-form" ).last().find('.kg').click();
                                    }
                                
                                }
                            });
                        }
                    }
                }
            });
            $(document).on('keyup','.has-many-vietnam-receive-form input.length', function(e) {
                let par = $(this).closest('tr');
                calculatorM3(par);
            });
            $(document).on('keyup','.has-many-vietnam-receive-form input.height', function(e) {
                let par = $(this).closest('tr');
                calculatorM3(par);
            });
            $(document).on('keyup','.has-many-vietnam-receive-form input.width', function(e) {
                let par = $(this).closest('tr');
                calculatorM3(par);
            });

            var mode = getUrlParameter('mode');

            if (mode != "" && mode == "reload") {
                window.location.href = "vietnam_receives";
            }


            function getUrlParameter(sParam) {
                var sPageURL = window.location.search.substring(1),
                    sURLVariables = sPageURL.split('&'),
                    sParameterName,
                    i;
            
                for (i = 0; i < sURLVariables.length; i++) {
                    sParameterName = sURLVariables[i].split('=');
            
                    if (sParameterName[0] === sParam) {
                        return typeof sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
                    }
                }
                return false;
            };

            function calculatorM3(par) {
                let length = par.children().find('input.length').val();
                let height = par.children().find('input.height').val();
                let width = par.children().find('input.width').val();

                length = parseInt(length.replace(/\,/g, ''));
                height = parseInt(height.replace(/\,/g, ''));
                width = parseInt(width.replace(/\,/g, ''));

                let m3 = 0.000;
                try {
                    let temp = (width * height * length) / 1000000;
                    temp = parseFloat(temp).toFixed(3);

                    m3 = temp;
                } catch (err) {
                    m3 = 0.000;
                }

                par.children().find('input.m3').val(m3);
                // console.log(length, "length");
                // console.log(height, "height");
                // console.log(width, "width");
            }
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

    public function gridOrder() {
        $headers = ['Mã đơn hàng', 'Mã vận đơn', 'Ảnh', 'Link SP', 'Kích thước', 'Màu', 'Đơn giá', 'Phí VCND', 'Tổng tiền SP', 'Trạng thái', 'Thao tác'];
        $rows = [];
        $table = new Table($headers, $rows);

        return $table;
    }
}
