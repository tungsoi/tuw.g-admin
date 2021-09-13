<?php

namespace App\Admin\Controllers\PurchaseOrder;

use App\Admin\Actions\PurchaseOrder\ConfirmOrderItem;
use App\Admin\Actions\PurchaseOrder\Deposite;
use App\Admin\Services\OrderService;
use App\Admin\Services\UserService;
use App\Jobs\HandleCustomerWallet;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\PurchaseOrder\PurchaseOrderItem;
use App\Models\PurchaseOrder\PurchaseOrderStatus;
use App\Models\System\Alert;
use App\Models\System\Warehouse;
use App\Models\TransportOrder\TransportCode;
use App\User;
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

class OfferController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Đàm phán mua hộ';

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
                    $filter->equal('supporter_id', 'Nhân viên kinh doanh')->select($service->GetListSaleEmployee());

                    $order_ids = DB::table('admin_role_users')->where('role_id', 4)->get()->pluck('user_id');
                    $filter->equal('supporter_order_id', 'Nhân viên đặt hàng')->select($service->GetListOrderEmployee());
                    
                    $filter->equal('warehouse_id', 'Kho nhận hàng')->select($service->GetListWarehouse());
                });
            }
            $filter->column(1/4, function ($filter) {
                $filter->between('created_at', 'Ngày tạo')->date();
                $filter->between('deposited_at', 'Ngày cọc')->date();
                $filter->between('order_at', 'Ngày đặt hàng');
            });
            $filter->column(1/4, function ($filter) {

                $filter->between('vn_receive_at', 'Ngày về Việt Nam');
                $filter->between('success_at', 'Ngày hoàn thành');
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

        $grid->order_number('Mã đơn hàng');
        $grid->current_rate('Tỷ giá');
        $grid->customer()->symbol_name('Mã khách hàng');

        $grid->status('Trạng thái')->display(function () {
            $data = [
                'status'        =>  [
                    'is_label'  =>  true,
                    'color'     =>  $this->statusText->label,
                    'text'      =>  $this->statusText->name . $this->countItemFollowStatus()
                ]
            ];
            return view('admin.system.core.list', compact('data'));
        });
        $grid->order_at("Ngày đặt");
        
        $grid->orderEmployee()->name('Nhân viên Order');

        $grid->sumItemPrice('Tiền thực đặt (Tệ) (1)')->display(function () {
            $price_rmb = $this->sumItemPrice();
            $price_vnd = str_replace(",", "", $this->sumItemPrice()) * $this->current_rate;
            $deposite = $price_vnd / 100 * 70;
            $data = [
                'amount_rmb'   =>  [
                    'is_label'   =>  false,
                    'text'      =>  $price_rmb
                ]
            ];            
            return view('admin.system.core.list', compact('data'));
        })->style('text-align: right; width: 100px;');

        $grid->sumShipFee('Tổng phí VCNĐ (Tệ) (2)')->display(function () {
            $data = [
                'amount_rmb'   =>  [
                    'is_label'   =>  false,
                    'text'      =>  $this->sumShipFee()
                ]
            ];            
            return view('admin.system.core.list', compact('data'));
        })->style('text-align: right; width: 100px;');

        $grid->amount('Tổng tiền thực đặt (3) = (1) + (2)')->display(function () {
            $price_rmb = str_replace(",", "", $this->sumItemPrice());
            $ship = $this->sumShipFee();

            $data = [
                'amount_rmb'   =>  [
                    'is_label'   =>  false,
                    'text'      =>  $price_rmb + $ship
                ]
            ];            
            return view('admin.system.core.list', compact('data'));
        })->style('text-align: right; width: 100px;');

        $grid->final_payment('Tiền thanh toán (Tệ) (4)')->editable();

        $grid->offer_cn('Chiết khấu (Tệ) (5) = (3) - (4)');
        $grid->offer_vn('Chiết khấu (VND) (6) = (5) * Tỷ giá đơn');
        $grid->internal_note('Ghi chú nội bộ')->editable();
        
        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->paginate(20);
        $grid->disableActions();

        Admin::script($this->offerOrderScript());

        return $grid;
    }

    public function form() {
        $form = new Form(new PurchaseOrder());
        $form->setTitle('TIỀN THANH TOÁN');
        $form->setAction(route('admin.purchase_orders.update'));

        $form->hidden('id');
        $form->tags('transport_code', "Mã vận đơn")->help('Mã đầu tiên được hiểu là MVD chính, các mã sau là MVD phụ.');
        $form->currency('final_payment', "Tệ thanh toán")->symbol('Tệ')->digits(2)->style('width', '100%');

        $form->currency('offer_cn', 'Chiết khẩu')->symbol('Tệ')->digits(2)->readonly()->style('width', '100%');
        $form->currency('offer_vn', 'Chiết khẩu')->symbol('VND')->digits(0)->readonly()->style('width', '100%');

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
        ');

        $form->saved(function (Form $form) {
            $id = $form->model()->id;
            $order = PurchaseOrder::find($id);
            $amount = $order->amount();

            $order->offer_cn = number_format($amount - $order->final_payment, 2);
            $order->offer_vn = number_format(($amount - $order->final_payment) * $order->current_rate, 0);
            $order->save();

            admin_toastr('Chỉnh sửa thành công', 'success');
            return redirect()->back();
        });

        return $form;
    }

    public function offerOrderScript() {
        $route = route('admin.purchase_orders.index');
        return <<<SCRIPT
        $('.column-final_payment a').each(function () {
            $(this).attr('data-url', "{$route}" + "/" + $(this).attr('data-pk'));
        });

        $('.column-internal_note a').each(function () {
            $(this).attr('data-url', "{$route}" + "/" + $(this).attr('data-pk'));
        });
SCRIPT;
    }
    
}