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
use Encore\Admin\Grid;

class TransportCodeController extends AdminController
{
    protected $title = "Mã vận đơn";

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
        $grid->model()->where('transport_code', '!=', "")
            ->orderBy('vietnam_receive_at', 'desc')
            ->orderBy('payment_at', 'asc')
            ->orderBy('export_at', 'asc')
            ->orderBy('customer_code_input', 'desc');
        
        // $grid->model()
        // ->orderBy('updated_at', 'desc');
        // ->orderBy('payment_at', 'asc')
        // ->orderBy('customer_code_input', 'desc');

        $userService = new UserService();
        $orderService = new OrderService();

        if (Admin::user()->isRole('customer')) {
            $orderIds = PaymentOrder::where('payment_customer_id', Admin::user()->id)->pluck('id')->toArray();
            $grid->model()->whereIn('order_id', $orderIds);
        }

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
                $filter->like('customer_code_input', 'Mã khách hàng');
                $filter->equal('status', 'Trạng thái')->select(TransportCodeStatus::pluck('name', 'id'));
            });

            $filter->column(1/4, function ($filter) {
                $filter->between('china_recevie_at', 'Ngày về TQ')->date();
                $filter->between('payment_at', 'Ngày thanh toán')->date();
            });

            $filter->column(1/4, function ($filter) {
                $filter->between('export_at', 'Ngày xuất kho')->date();
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
        $grid->order_id('Mã đơn hàng')->style('width: 100px')->display(function () {
            $html = "<input type='hidden' value='$this->id' id='id' />";
            return $html .=  $this->paymentOrder->order_number ?? null;
        });
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
                    'style'     =>  'color: black !important;'
                ]
            ];
            return view('admin.system.core.list', compact('data'));
        })->style('color: black !important;');
        $grid->customer_payment('Khách hàng thanh toán')->style('width: 100px')->display(function () {
            return $this->paymentOrder->paymentCustomer->symbol_name ?? "";
        });
        $grid->kg('Cân nặng (kg)')->totalRow(function ($amount) {
            return number_format($amount, 2);
        });
        $grid->length('Dài (cm)');
        $grid->width('Rộng (cm)');
        $grid->height('Cao (cm)');
        $grid->v('V/6000')->display(function () {
            return $this->v();
        });
        $grid->m3('M3')->display(function () {
            return $this->m3();
        });
        $grid->advance_drag('Ứng kéo (Tệ)')->style('max-width: 100px');
        $grid->price_service('Giá vận chuyển')->display(function () {
            return number_format($this->price_service); 
        })->style('max-width: 100px');
        $grid->payment_type('Loại thanh toán')->display(function () {
            return $this->paymentType();
        })->style('max-width: 100px');
        $grid->amount('Tổng tiền')->display(function ()  {
            $amount = $this->amount();

            return $amount == 0 ? "<span style='color: red'>0</span>" : number_format($amount);
            
        })->style('max-width: 100px');
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

        $grid->payment_at('Ngày thanh toán')->display(function () {
            if ($this->payment_at != null) {
                return date('H:i d-m-Y', strtotime($this->payment_at));
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
                $filter->like('customer_code_input', 'Mã khách hàng');
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
            return $this->m3();
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
