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
use Encore\Admin\Widgets\Table;

class PaymentOrderZeroController extends AdminController
{
    protected $title = "Đơn hàng thanh toán có tổng tiền = 0 VND";

    public function grid() {
        $grid = new Grid(new PaymentOrder());

        $grid->model()->where('amount', 0)
        ->where('is_sub_customer_wallet_weight', 0)
        ->where('status', 'payment_export')
        ->whereNotIn('payment_customer_id', [577, 1841])
        ->orderBy('id', 'desc');

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
                    $filter->where(function ($query) {
                        $ware_house_id = $this->input;

                        $orderIds = TransportCode::whereNotNull('order_id')
                        ->where('ware_house_id', $ware_house_id)
                        ->get()
                        ->unique('order_id')
                        ->pluck('order_id');

                        $query->whereIn('id', $orderIds);
                    }, 'Kho hàng thanh toán', 'ware_house_id')->select($service->GetListWarehouse());
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
        $grid->order_number('Mã đơn hàng')->width(100)->label('primary');
        $grid->status('Trạng thái')->display(function () {
            $data = [
                'amount_rmb'   =>  [
                    'is_label'   =>  true,
                    'color'     =>  $this->statusColor(),
                    'text'      =>  $this->statusText()
                ]
            ];

            if ($this->status == "cancel") {
                $data[] = [
                    'is_label'  =>  false,
                    'text'  =>  $this->user_cancel_id != null ? User::find($this->user_cancel_id)->name : ""
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
        $grid->symbol_name('KKhách hàng thanh toán')->display(function () {
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
                return str_replace('.0', '', number_format($this->total_sub_wallet_weight, 2, '.', ''));
            } else {
                return 0;
            }
        });
        $grid->total_m3('Số khối');
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
            if ($this->export_at == "") {
                return null;
            }
            return date('H:i | d-m-Y', strtotime($this->export_at));
        })->style('text-align: center');
        $grid->inernal_note('Ghi chú');

        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->paginate(20);
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableEdit();
            $actions->disableDelete();
            $actions->disableView();

            $actions->append(new BtnView($this->row->id, route('admin.payments.showRebuild', $this->row->id)));

            // if ($this->row->status == 'payment_not_export' && ! Admin::user()->isRole('customer')) {
            //     $route = route('admin.payments.exportOrder'); // route export
            //     $actions->append(new ExportTransportCode($this->row->id, $route));
            // }

            // if (! Admin::user()->isRole('customer')) {
            //     $actions->append(new Recharge($this->row->payment_customer_id));

            //     if ($this->row->status == "payment_not_export") {
            //         $actions->append(new Cancel($this->row->id));
            //     }
                
            // }
        });

        Admin::script(
            <<<EOT
            $('tfoot').each(function () {
                $(this).insertAfter($(this).siblings('thead'));
            });
EOT);

        return $grid;
    }
}
