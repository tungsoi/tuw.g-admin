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
        
        // $sub = PurchaseOrderItem::select('shop_name')->whereNull('order_id')
        //         ->where('customer_id', Admin::user()->id)
        //         ->whereStatus(PurchaseOrderItemStatus::whereCode('in_cart')->first()->id)
        //         ->orderBy('id', 'desc');

        // $shops = DB::table(DB::raw("({$sub->toSql()}) as sub"))
        //     ->select('shop_name')
        //     ->groupBy('shop_name')
        //     ->get();

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
        $grid->actions(function ($actions) {
            // $actions->disableAll();
            // $actions->disableView();
            // $actions->disableEdit();
            // $actions->disableDelete();
            
            // $actions->append('
            // <a href="'.route('admin.carts.edit', $this->getKey()).'" class="btn btn-xs btn-info ">
            //     <i class="fa fa-edit"></i> Sửa
            // </a>');
            // $actions->append('
            //     <a class="btn btn-xs btn-danger btn-customer-delete-item" data-id="'.$this->getKey().'">
            //         <i class="fa fa-trash"></i><span class="hidden-xs">&nbsp; Xoá</span>
            //     </a>
            // ');
        });
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
        $show = new Show(OrderItem::findOrFail($id));

        $show->field('id', trans('admin.id'));
        $show->title(trans('admin.title'));
        $show->order(trans('admin.order'));
        $show->field('created_at', trans('admin.created_at'));
        $show->field('updated_at', trans('admin.updated_at'));

        return $show;
    }

    /**
     * Edit interface.
     *
     * @param mixed   $id
     * @param Content $content
     *
     * @return Content
     */
    public function addCart($id, Content $content)
    {
        return $content
            ->title($this->title())
            ->description($this->description['edit'] ?? trans('admin.edit'))
            ->body($this->formEdit((int) $id)) ;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new PurchaseOrderItem);
        
        // if (session()->has('booking_product')) {
        //     $booking = session()->get('booking_product');

        //     $form->text('shop_name', 'Tên shop')->default($booking[0]['shop_name']);
        //     $form->text('product_name', 'Tên sản phẩm')->default($booking[0]['product_name']);
        //     $form->text('product_link', 'Link sản phẩm')->rules('required')->default($booking[0]['product_link']);
        //     $form->html('<img src="'.$booking[0]['product_image'].'" style="width: 150px;"/>');

        //     $form->text('product_size', 'Size sản phẩm')->rules('required')->default($booking[0]['product_size']);
        //     $form->text('product_color', 'Màu sắc sản phẩm')->rules('required')->default($booking[0]['product_color']);
        //     $form->number('qty', 'Số lượng')->rules('required')->default($booking[0]['qty']);
        //     $form->currency('price', 'Giá sản phẩm (Tệ)')->rules('required')->symbol('￥')->digits(2)->default($booking[0]['price']);
        //     $form->textarea('customer_note', 'Ghi chú của bạn');
        //     $form->hidden('customer_id')->default(Admin::user()->id);
        //     $form->hidden('status')->default(OrderItem::PRODUCT_NOT_IN_CART);
        //     $form->hidden('qty_reality');
        //     $form->hidden('product_image')->default($booking[0]['product_image']);
    
        //     $form->disableEditingCheck();
        //     $form->disableCreatingCheck();
        //     $form->disableViewCheck();
    
        //     $form->saving(function (Form $form) {
        //         $form->qty_reality = $form->qty;
        //     });
    
        //     return $form;
        // }
        
        $form->text('shop_name', 'Tên shop')->rules('required');
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

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function formEdit($id = "")
    {
        $form = new Form(new OrderItem);

        $item = OrderItem::find($id);
        $form->setAction(route('admin.carts.storeAddByTool'));

        $form->text('shop_name', 'Tên shop')->default($item->shop_name);
        $form->text('product_name', 'Tên sản phẩm')->default($item->product_name);
        $form->text('product_link', 'Link sản phẩm')->rules('required')->default($item->product_link);
        $form->html('<img src="'.$item->product_image.'" style="width: 150px;"/>');

        $form->text('product_size', 'Size sản phẩm')->rules('required')->default($item->product_size);
        $form->text('product_color', 'Màu sắc sản phẩm')->rules('required')->default($item->product_color);
        $form->number('qty', 'Số lượng')->rules('required')->default($item->qty);
        $form->currency('price', 'Giá sản phẩm (Tệ)')->rules('required')->symbol('￥')->digits(2)->default($item->price);
        $form->textarea('customer_note', 'Ghi chú');
        // $form->hidden('customer_id')->default(Admin::user()->id);
        $form->hidden('status')->default(OrderItem::PRODUCT_NOT_IN_CART);
        $form->hidden('qty_reality');
        $form->hidden('product_image')->default($item->product_image);

        $form->hidden('xid','Id')->default($item->id);

        $form->disableEditingCheck();
        $form->disableCreatingCheck();
        $form->disableViewCheck();

        return $form;
    }

    public function storeAddByTool(Request $request)
    {
        # code...

        $data = $request->all();
        $data['customer_id'] = Admin::user()->id;
        $data['qty_reality'] = $data['qty'];
        OrderItem::find($data['xid'])->update($data);

        admin_toastr('Lưu thành công !', 'success');
        return redirect()->route('admin.carts.index');
    }

    public function addCart1688($id, Content $content)
    {
        return $content
            ->title($this->title())
            ->description($this->description['edit'] ?? trans('admin.edit'))
            ->body($this->formEdit1688((string) $id)) ;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function formEdit1688($id = "")
    {
        $ids = explode(',', $id);
        $items = OrderItem::whereIn('id', $ids)->get();

        return view('admin.cart1688', compact('items'))->render();
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
                'price' =>  $data['price'][$item_id],
                'customer_note' =>  $data['customer_note'][$item_id],
                'qty_reality'   =>  $data['qty'][$item_id],
                'customer_id'   =>  Admin::user()->id,
                'status'  =>  OrderItem::PRODUCT_NOT_IN_CART
            ];

            OrderItem::find($item_id)->update($res);
        }

        admin_toastr('Lưu thành công', 'success');
        return redirect()->route('admin.carts.index');
    }
}
