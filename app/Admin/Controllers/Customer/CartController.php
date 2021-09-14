<?php

namespace App\Admin\Controllers\Customer;

// use App\Admin\Actions\Customer\CreateOrderFromCart;

use App\Admin\Actions\PurchaseOrder\CreateOrderFromCart;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\OrderItem;
use App\Models\PurchaseOrder\PurchaseOrderItem;
use App\Models\PurchaseOrder\PurchaseOrderItemStatus;
use App\Models\SyncData\AloorderPurchaseOrderItem;
use App\Models\System\ExchangeRate;
use App\Models\System\Warehouse;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
Use Encore\Admin\Widgets\Table;
use Illuminate\Support\Facades\DB;

class CartController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title;

    public function __construct()
    {
        $this->title = trans('Giỏ hàng');
    }

    /**
     * Index interface.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->title($this->title())
            ->description('Danh sách sản phẩm trong giỏ')
            ->body($this->grid());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new PurchaseOrderItem());
        $grid->model()->where('customer_id', -1);

        $shops = PurchaseOrderItem::select('shop_name')
                ->whereNull('order_id')
                ->where('customer_id', Admin::user()->id)
                ->whereStatus(PurchaseOrderItemStatus::whereCode('in_cart')->first()->id)
                ->orderBy('id', 'desc')
                ->get()
                ->unique('shop_name');

        $items = [];
        foreach ($shops as $shop) {
            $shopName = ($shop->shop_name == null) ? "Không tên" : $shop->shop_name;
            $items[$shopName] = [
                'shop_name' =>  $shopName,
                'items'     =>  PurchaseOrderItem::whereNull('order_id')
                                ->whereShopName($shopName)
                                ->where('customer_id', Admin::user()->id)
                                ->whereStatus(PurchaseOrderItemStatus::whereCode('in_cart')->first()->id)
                                ->get()
            ];
        }


        $grid->header(function () use ($items) {
            $exchange_rates = ExchangeRate::first()->vnd;
            $warehouses = Warehouse::whereIsActive(1)->get();

            return view('admin.system.customer.cart', compact('items', 'exchange_rates', 'warehouses'));
        });

        $grid->disableColumnSelector();
        $grid->disableExport();
        $grid->disableFilter();
        $grid->disableActions();
        $grid->disableBatchActions();
        $grid->disablePagination();
        $grid->disableCreateButton();
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append('
                <a href="'.route('admin.carts.create').'" class="btn btn-sm btn-success">
                    <i class="fa fa-plus"></i><span class="hidden-xs">&nbsp;&nbsp;Thêm sản phẩm vào giỏ</span>
                </a>
            ');

            $tools->append("
                <a class='btn-create-order btn btn-sm btn-danger'>
                    <i class='fa fa-cart-plus'></i>
                    &nbsp; Tạo đơn hàng
                </a>
            ");
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed   $id
     * @return Show
     */
    protected function detail($id)
    {
        $ids = explode(',', $id);
        $items = PurchaseOrderItem::whereIn('id', $ids)->get();

        return view('admin.system.customer.booking', compact('items'))->render();
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new PurchaseOrderItem);
        
        $form->text('shop_name', 'Tên shop');
        $form->text('product_name', 'Tên sản phẩm');
        $form->text('product_link', 'Link sản phẩm')->rules('required');
        $form->image('product_image','Ảnh sản phẩm')->thumbnail('small', $width = 150, $height = 150);
        $form->text('product_size', 'Size sản phẩm')->rules('required');
        $form->text('product_color', 'Màu sắc sản phẩm')->rules('required');
        $form->number('qty', 'Số lượng')->rules('required')->default(1);
        $form->currency('price', 'Giá sản phẩm (Tệ)')->rules('required')->symbol('￥')->digits(2);
        $form->textarea('customer_note', 'Ghi chú của bạn');
        $form->hidden('customer_id')->default(Admin::user()->id);
        $form->hidden('status')->default(PurchaseOrderItemStatus::whereCode('in_cart')->first()->id);
        $form->hidden('qty_reality');

        $form->disableEditingCheck();
        $form->disableCreatingCheck();
        $form->disableViewCheck();

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });

        $form->saving(function (Form $form) {
            $form->qty_reality = $form->qty;
        });

        return $form;
    }

    public function storeAdd1688(Request $request)
    {
        # code...
        $data = $request->all();
        foreach ($data['id'] as $item_id => $raw) {
            $res = [
                'shop_name' =>  $data['shop_name'][$item_id],
                'product_name' =>  $data['product_name'][$item_id],
                'product_link' =>  $data['product_link'][$item_id],
                'product_size' =>  $data['product_size'][$item_id],
                'product_color' =>  $data['product_color'][$item_id],
                'qty' =>  $data['qty'][$item_id],
                'price' =>  str_replace(',', '', $data['price'][$item_id]),
                'customer_note' =>  $data['customer_note'][$item_id],
                'qty_reality'   =>  $data['qty'][$item_id],
                'customer_id'   =>  Admin::user()->id,
                'status'  => 10
            ];

            PurchaseOrderItem::find($item_id)->update($res);
        }

        admin_toastr('Lưu thành công', 'success');
        return redirect()->route('admin.carts.index');
    }
}
