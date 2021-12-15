<?php

namespace App\Admin\Controllers\Customer;

// use App\Admin\Actions\Customer\CreateOrderFromCart;

use App\Admin\Actions\Customer\CreateOrderInCart;
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
        $grid->model()->where('customer_id', Admin::user()->id)
        ->whereStatus(10)
        ->orderBy('id', 'desc');

        $grid->header(function () {
            $exchange_rates = ExchangeRate::first()->vnd;
            $warehouses = Warehouse::whereIsActive(1)->get();

            return view('admin.system.customer.cart', compact('exchange_rates', 'warehouses'))->render();
        });
        // $grid->id('#')->display(function () {
        //     return '<input type="checkbox" class="choose-item" id="" data-index="'.$this->id.'" style="width: 25px !important; height: 25px !important; cursor: pointer;"">';
        // });
        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT');
        $grid->shop_name("Tên Shop")->width(200)->sortable()->display(function () {
            return "{$this->shop_name}";
        });
        $grid->product_image('Ảnh sản phẩm')->lightbox(['width' => 100, 'height' => 100])->width(120);
        $grid->product_name("Tên sản phẩm")->width(200)->display(function () {
            return "{$this->product_name}";
        });
        $grid->product_link('Link')->display(function () {
            return "<a href=".$this->product_link." target='_blank'>Xem </a>";
        })->width(100);
        $grid->product_size("Size")->display(function () {
            return "{$this->product_size}";
        });
        $grid->product_color("Màu")->display(function () {
            return "{$this->product_color}";
        });
        $grid->qty("Số lượng");
        $grid->price("Đơn giá (Tệ)")->display(function () {
            $price = $this->price;
            if (strpos($price, ",") !== false && strpos($price, ".") !== false) {
                $price = str_replace(",", "", $price);
            } else {
                if (strpos($price, ",") !== false) {
                    $price = str_replace(",", ".", $price);
                }
            }
            $price = (float) $price;
            try {
                $price = number_format($price, 2, '.', '');
            } catch (\Exception $e) {
                $price = 0;
            }

            return $price;
        });

        $grid->amount("Thành tiền (Tệ)")->display(function () {
            $qty = $this->qty;

            if (! is_numeric($this->qty)) {
                $this->update([
                    'qty'   =>  1,
                    'qty_reality'   =>  1,
                ]);
                $qty = 1;
            }
            $price = $this->price;
            if (strpos($price, ",") !== false && strpos($price, ".") !== false) {
                $price = str_replace(",", "", $price);
            } else {
                if (strpos($price, ",") !== false) {
                    $price = str_replace(",", ".", $price);
                }
            }
            $price = (float) $price;
            try {
                $price = number_format($price, 2, '.', '');
                return '<span class="item-price" data-index="'.$this->id.'">'.str_replace(",", "", number_format($qty * $price, 2)).'</span>';

            } catch (\Exception $e) {
                dd($this->id);
                $price = 0;
            }
        });
        $grid->customer_note("Ghi chú");
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->append('<a href="'.route('admin.carts.edit', $this->row->id).'" class="grid-row-edit btn btn-xs btn-warning" data-toggle="tooltip" title="" data-original-title="Chỉnh sửa"><i class="fa fa-edit"></i></a>');

            $actions->disableView();
            $actions->disableDelete();
            $actions->disableEdit();
            // $actions->append('<a href="javascript:void(0);" data-url="'.route('admin.carts.destroy', $this->row->id).'" data-id="{{ $item_ele->id }}" class="grid-row-custom-delete btn btn-xs btn-danger" data-toggle="tooltip" title="Xóa"><i class="fa fa-trash"></i></a>');
        });
        // PurchaseOrderItem::where('customer_id', Admin::user()->id)
        //     ->whereNull('shop_name')
        //     ->update([
        //         'shop_name' =>  'Không tên'
        //     ]);

        // $shops = PurchaseOrderItem::select('shop_name')
        //         ->whereNull('order_id')
        //         ->where('customer_id', Admin::user()->id)
        //         ->whereStatus(10)
        //         ->orderBy('id', 'desc')
        //         ->get()
        //         ->unique('shop_name');

        // $items = [];
        // foreach ($shops as $shop) {
        //     $shopName = ($shop->shop_name == null) ? "Không tên" : $shop->shop_name;
        //     $items[$shopName] = [
        //         'shop_name' =>  $shopName,
        //         'items'     =>  PurchaseOrderItem::whereNull('order_id')
        //                         ->whereShopName($shopName)
        //                         ->where('customer_id', Admin::user()->id)
        //                         ->whereStatus(10)
        //                         ->get()
        //     ];
        // }


        // $grid->header(function () use ($items) {
        //     $exchange_rates = ExchangeRate::first()->vnd;
        //     $warehouses = Warehouse::whereIsActive(1)->get();

        //     return view('admin.system.customer.cart', compact('items', 'exchange_rates', 'warehouses'));
        // });

        $grid->disableColumnSelector();
        $grid->disableExport();
        $grid->disableFilter();
        $grid->disableCreateButton();
        // $grid->disableBatchActions();
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append('
                <a href="'.route('admin.carts.create').'" class="btn btn-sm btn-success">
                    <i class="fa fa-plus"></i><span class="hidden-xs">&nbsp;&nbsp;Thêm sản phẩm vào giỏ</span>
                </a>
            ');

            // $tools->append("
            //     <a class='btn-create-order btn btn-sm btn-danger'>
            //         <i class='fa fa-cart-plus'></i>
            //         &nbsp; Tạo đơn hàng
            //     </a>
            // ");

            $tools->append( new CreateOrderInCart());
        });


        Admin::script($this->scriptGrid());

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

    public function destroy($id)
    {
        $ids = explode(",", $id);
        PurchaseOrderItem::whereIn('id', $ids)->update([
            'status'    =>  99
        ]);

        return response()->json([
            'status'    =>  true,
            'message'   =>  "Xoá thành công"
        ]);
    }

    public function scriptGrid() {

        $exchange_rates = ExchangeRate::first()->vnd;
        return <<<SCRIPT
        console.log('grid js');

        $("input.grid-row-checkbox").on("ifChanged", function () {
            let total = 0;
            var key = $.admin.grid.selected();
        
            if (key.length !== 0) {
                var i;
                for (i = 0; i < key.length; ++i) {
                    let data_key = key[i];

                    let tr_ele = $('tr[data-key="'+data_key+'"]');
                    let item = tr_ele.find('.item-price').html();
                    item = item.replace(/,/g, "");
                    item = parseInt(item);

                    total += item;
                }

                console.log(total, "total");
            }

            // let total_deposite_formated = number_format(total_deposite);
            let total_rmb = number_format(total, 2);
            let rate = {$exchange_rates};
            let total_vnd = number_format(total_rmb * rate);

            $('.estimate-amount-vnd').html(total_vnd);
            $('.estimate-amount').html(total_rmb);
            
            // $('input#estimate-deposited').val(total_deposite_formated + " VND");
        });

        function number_format(number, decimals, dec_point, thousands_sep) {
            // Strip all characters but numerical ones.
            number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
            var n = !isFinite(+number) ? 0 : +number,
                prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
                dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
                s = '',
                toFixedFix = function (n, prec) {
                    var k = Math.pow(10, prec);
                    return '' + Math.round(n * k) / k;
                };
            // Fix for IE parseFloat(0.55).toFixed(0) = 0;
            s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            }
            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }
            return s.join(dec);
        }

        
 
SCRIPT;
    }
}
