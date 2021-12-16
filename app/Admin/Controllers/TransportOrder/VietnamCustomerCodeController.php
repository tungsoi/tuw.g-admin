<?php

namespace App\Admin\Controllers\TransportOrder;

use App\Admin\Actions\TransportCode\ConfirmSwapWarehouse;
use App\Admin\Actions\TransportCode\Export;
use App\Admin\Actions\TransportCode\Payment;
use App\Admin\Actions\TransportCode\PaymentExport;
use App\Admin\Actions\TransportCode\PaymentNotExport;
use App\Admin\Actions\TransportCode\SwapWarehouse;
use App\Admin\Services\OrderService;
use App\Admin\Services\UserService;
use App\Models\PaymentOrder\PaymentOrder;
use App\Models\System\Alert;
use App\Models\System\Warehouse;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Illuminate\Http\Request;
use App\Models\TransportOrder\TransportCode;
use App\Models\TransportOrder\TransportCodeStatus;
use App\User;
use Encore\Admin\Grid;
Use Encore\Admin\Widgets\Table;

class VietnamCustomerCodeController extends AdminController
{
    protected $title = "Mã khách hàng về Việt nam";

    public function search($transportCode) {
        $data = TransportCode::whereTransportCode($transportCode)->first();
        return response()->json([
            'code'  =>  200,
            'data'  =>  $data
        ]);
    }

    public function grid() 
    {
        $grid = new Grid(new TransportCode());

        $grid->filter(function($filter) {
            $filter->expand();
            $filter->disableIdFilter();
            $filter->column(1/4, function ($filter) {
                
                $filter->like('transport_code', 'Mã vận đơn');
                
            });
            $filter->column(1/4, function ($filter) {
                $filter->like('customer_code_input', 'Mã khách hàng');
            });

            $filter->column(1/4, function ($filter) {
                $filter->between('china_recevie_at', 'Ngày về TQ')->date();
            });

            $filter->column(1/4, function ($filter) {
                $filter->between('vietnam_recevie_at', 'Ngày về VN')->date();
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
            return "Tổng hợp mã vận đơn đã về kho Việt Nam theo mã khách hàng";
        });

        $all_codes = TransportCode::where('status', 1)->get();
        $all_code_ids = $all_codes->pluck('id');
        $all_customer_codes = $all_codes->pluck('customer_code_input', 'id');

        if ($all_customer_codes->count() > 0) {
            $all_customer_codes = array_unique($all_customer_codes->toArray());
            $ids = array_keys($all_customer_codes);

            $grid->model()->whereIn('id', $ids)->with('paymentOrder')->orderBy('vietnam_receive_at', 'desc');

            $grid->rows(function (Grid\Row $row) {
                $row->column('number', ($row->number+1));
            });
            $grid->column('number', 'STT');
            $grid->customer_code_input('Mã khách hàng')->display(function () {
                $data = [
                    'order_number'   =>  [
                        'is_link'   =>  true,
                        'route'     =>  route('admin.transport_codes.index'). "?customer_code_input=". $this->customer_code_input."&query_customer_code_input=equal&status=1",
                        'text'      =>  $this->customer_code_input,
                        'style'     =>  'color: green !important;'
                    ]
                ];
                return view('admin.system.core.list', compact('data'));
            })->style('color: black !important;');

            $grid->id('Số mã vận đơn')->display(function () use ($all_codes) {
                return "(".$all_codes->where('customer_code_input', $this->customer_code_input)->count().")";
            })->expand(function ($model) use ($all_codes) {
                $header = ["MÃ ĐƠN HÀNG", "MÃ VẬN ĐƠN", "MKH", "CÂN NẶNG (KG)", "DÀI (CM)", "RỘNG (CM)", "CAO (CM)", "V/6000", "M3", "ỨNG KÉO (TỆ)", "VỀ KHO TQ", "VỀ KHO VN", "TRẠNG THÁI", "KHO HÀNG"];
                
                $codes = $all_codes->where('customer_code_input', $model->customer_code_input);

                $info  = [];
                foreach ($codes as $code) {
                    $info[] = [
                        $code->paymentOrder->order_number ?? null,
                        $code->transport_code,
                        $code->customer_code_input,
                        $code->kg,
                        $code->length,
                        $code->width,
                        $code->height,
                        $code->v(),
                        $code->m3_cal(),
                        $code->advance_drag,
                        $code->china_receive_at != null ? date('H:i d-m-Y', strtotime($code->china_receive_at)) : null,
                        $code->vietnam_receive_at != null ? date('H:i d-m-Y', strtotime($code->vietnam_receive_at)) : null,
                        $code->statusText->name,
                        $code->warehouse->name ?? ""
                    ];
                }
            
                return new Table( $header, $info);
            })->style('width: 50%;');
            
        }

        $grid->expandFilter();
        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->paginate(10);

        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function(Grid\Tools\BatchActions $actions) {
                $actions->disableDelete();
            });
        });

        $grid->disableActions();
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
            $actions->disableDelete();
            $actions->disableEdit();
        });

        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new TransportCode);

        $form->text('customer_code_input', 'Mã khách hàng vận đơn');
        $form->text('transport_code', "Mã vận đơn")->rules(['required']);

        $form->currency('kg', "KG")->rules(['required'])->digits(1)->symbol('KG');
        $form->number('length', "Dài (cm)")->rules(['required']);
        $form->number('width', "Rộng (cm)")->rules(['required']);
        $form->number('height', "Cao (cm)")->rules(['required']);
        $form->currency('advance_drag', "Ứng kéo")->rules(['required'])->symbol('Tệ');
        $form->text('admin_note', 'Admin ghi chú');

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
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

    public function gridFilterPortal() 
    {
        $grid = new Grid(new TransportCode());

        $grid->model()->where('transport_code', '!=', "")
            ->orderBy('vietnam_receive_at', 'desc')
            ->orderBy('payment_at', 'asc')
            ->orderBy('export_at', 'asc')
            ->orderBy('customer_code_input', 'desc');

        if (! isset($_GET['transport_code'])) {
            $grid->model()->where('transport_code', '-111');
        }

        $userService = new UserService();
        $orderService = new OrderService();

        $grid->expandFilter();
        $grid->filter(function($filter) use ($userService, $orderService) {
            $filter->expand();
            $filter->disableIdFilter();
            $filter->column(1/4, function ($filter) use ($userService) {
                
                $filter->like('transport_code', 'Mã vận đơn');

                if (! Admin::user()->isRole('customer')) {
                    $filter->equal('customer_id', 'Khách hàng thanh toán')->select($userService->GetListCustomer());
                }
                
            });
            $filter->column(1/4, function ($filter) use ($orderService)  {
                $filter->equal('customer_code_input', 'Mã khách hàng');
            });

            $filter->column(1/4, function ($filter) {
                $filter->equal('status', 'Trạng thái')->select(TransportCodeStatus::pluck('name', 'id'));
            });

            $filter->column(1/4, function ($filter) {
                $filter->between('vietnam_recevie_at', 'Ngày về VN')->date();
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

        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function(Grid\Tools\BatchActions $actions) {
                $actions->disableDelete();
            });
            if (! Admin::user()->isRole('customer')) {
                $tools->append(new SwapWarehouse());
                $tools->append(new ConfirmSwapWarehouse());
                // $tools->append(new Payment());
                $tools->append(new PaymentNotExport());
                $tools->append(new PaymentExport());
                $tools->append(new Export());
            }
            
        });

        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT');
        $grid->transport_code('Mã vận đơn')->style('max-width: 150px')->display(function () {
            $data = [
                'order_number'   =>  [
                    'is_label'   =>  false,
                    'text'      =>  $this->transport_code
                ],
                'purchase_orders' => [
                    'is_link'  =>  true,
                    'route' =>  route('admin.purchase_orders.index') . "?order_number=".$this->getOrdernNumberPurchase(),
                    'text'      =>  $this->getOrdernNumberPurchase()
                ]
            ];
            return view('admin.system.core.list', compact('data'));
        });
        $grid->customer_code_input('Mã khách hàng')->display(function () {
            $data = [
                'order_number'   =>  [
                    'is_link'   =>  true,
                    'route'     =>  route('admin.transport_codes.index'). "?customer_code_input=". $this->customer_code_input,
                    'text'      =>  $this->customer_code_input,
                    'style'     =>  'color: black'
                ]
            ];
            return view('admin.system.core.list', compact('data'));
        })->style('color: black !important;');
        $grid->customer_payment('Khách hàng thanh toán')->style('width: 100px')->display(function () {
            return $this->paymentOrder->paymentCustomer->symbol_name ?? "";
        });
        $grid->kg('Cân nặng (kg)');
        $grid->length('Dài (cm)');
        $grid->width('Rộng (cm)');
        $grid->height('Cao (cm)');
        $grid->v('V/6000')->display(function () {
            return $this->v();
        });
        $grid->m3('M3')->display(function () {
            return $this->m3_cal();
        });
        $grid->advance_drag('Ứng kéo (Tệ)')->style('max-width: 100px');
        
        $grid->china_receive_at('Về kho TQ')->display(function () {
            if ($this->china_receive_at != null) {
                return date('H:i d-m-Y', strtotime($this->china_receive_at));
            }
        });
        $grid->vietnam_receive_at('Về kho VN')->display(function () {
            if ($this->vietnam_receive_at != null) {
                return date('H:i d-m-Y', strtotime($this->vietnam_receive_at));
            }
        });
        $grid->status('Trạng thái')->display(function () {
            $data = [
                'order_number'   =>  [
                    'is_label'  =>  true,
                    'color'     =>  $this->statusText->label,
                    'text'      =>  $this->statusText->name
                ],
                'time'  =>  [
                    'is_label'  =>  false,
                    'text'      =>  $this->getTimeline()
                ]
            ];
            return view('admin.system.core.list', compact('data'));
        });

        $grid->ware_house_id('Kho hàng')->display(function () use ($orderService) {
            if ($this->status == $orderService->getTransportCodeStatus('swap')) {
                return ($this->warehouse->name ?? "") . " => " . ($this->warehouseSwap->name ?? "");
            }

            return $this->warehouse->name ?? "";
        })->style('max-width: 150px');

        if (! Admin::user()->isRole('customer')) {
            $grid->admin_note('Ghi chú');
        } else {
            $grid->disableActions();
        }
        

        $grid->disableCreateButton();
        $grid->disableExport();

        if (Admin::user()->isRole('customer')) {
            $grid->disableBatchActions();
        }
        $grid->disableColumnSelector();
        $grid->paginate(20);

        $grid->tools(function (Grid\Tools $tools) {
            $tools->batch(function(Grid\Tools\BatchActions $actions) {
                $actions->disableDelete();
            });
        });

        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
            $actions->disableDelete();

            $orderService = new OrderService();
            if (in_array($this->row->status, [$orderService->getTransportCodeStatus('wait-payment'), $orderService->getTransportCodeStatus('payment')])) {
                $actions->disableEdit();
            }

            if (! in_array($this->row->status, [$orderService->getTransportCodeStatus('vietnam-rev'), $orderService->getTransportCodeStatus('swap'), $orderService->getTransportCodeStatus('not-export')])) {
                Admin::script(
                    <<<EOT
                    $('input[data-id={$this->row->id}]').parent().parent().empty();
EOT);
            }
        });

        return $grid;
    }

}
