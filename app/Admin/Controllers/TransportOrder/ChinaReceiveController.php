<?php

namespace App\Admin\Controllers\TransportOrder;

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

class ChinaReceiveController extends AdminController
{
    protected $title = "Bắn hàng Trung quốc nhận";

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
                    $column->append((new Box('Mã vận đơn lưu thành công', $this->grid()))); 
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
        $headers = ['Mã vận đơn', 'Trạng thái'];
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

        $form->setTitle('Nhập thông tin mã vận đơn');

        $form->html('<h4>Người thực hiện: ' . Admin::user()->name . "</h4>");
        $form->html(view('admin.system.transport_order.alert'));

        $form->table('china-receive', '', function ($table) {
            $table->text('transport_code', 'Mã vận đơn')->autofocus();
            $table->text('kg', 'Cân nặng (kg)')->default(0)->readonly();
            $table->text('length', 'Dài (cm)')->default(0)->readonly();
            $table->text('width', 'Rộng (cm)')->default(0)->readonly();
            $table->text('height', 'Cao (cm)')->default(0)->readonly();
            $table->text('advance_drag', 'Ứng kéo (cm)')->default(0)->readonly();
        });

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
            $tools->disableList();
        });

        $form->disableSubmit();
        $form->disableReset();

        Admin::style('
            form .col-sm-2, form .col-sm-8 {
                width: 100%;
                text-align: left !important;
                padding: 0px !important;
            }

            #has-many-china-receive .input-group-addon {
                display: none;
            }

            #has-many-china-receive .add {
                display: none;
            }

            .box {
                border: none !important;
            }
            .input-group {
                width: 100%;
            }

            .has-many-china-receive-form td:nth-child(1) {
                min-width: 300px !important;
            }

            form .col-sm-2 {
                display: none;
            } 

            form table input:focus {
                background: wheat !important;
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
            $('#has-many-china-receive .add').click();

            $(document).on('keydown','.has-many-china-receive-form input', function(e) {
                if (e.which == 13) 
                {
                    // call ajax submit
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    }); 
                    
                    let value = $( ".has-many-china-receive-form" ).last().find('.transport_code').val();

                    $.ajax({
                        url: "{$route}",
                        type: 'POST',
                        dataType: "JSON",
                        data: {
                            transport_code: value
                        },
                        success: function (response)
                        {   
                            console.log(value, "value");

                            if (! response.status) {
                                $.admin.toastr.error('Mã vận đơn đã được nhập.', '', {timeOut: 5000});
                                $( ".has-many-china-receive-form" ).last().find('.transport_code').val("");
                            } else {
                                $.admin.toastr.success(response.message, '', {timeOut: 2000});

                                $( ".has-many-china-receive-form" ).last().find('.transport_code').prop('disabled', true);
                                $('#has-many-china-receive .add').click();
                                $( '.col-md-4 tbody' ).append(
                                    "<tr> <td>"+response.data.transport_code+"</td> <td style='color:green;'>Đã lưu</td>  </tr>"
                                );
                            }
                        }
                    });


                    // fail -> hien thi box error
                    // success -> next row -> disable dong hien tai

                    setTimeout(function () {
                        $( ".has-many-china-receive-form" ).last().find('.transport_code').focus();
                    }, 500);
                }
            } );
        });
SCRIPT;
    }
}
