<?php

namespace App\Admin\Controllers\Customer;

use App\Admin\Controllers\PurchaseOrder\ItemController;
use App\Admin\Services\OrderService;
use App\Admin\Services\UserService;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\PurchaseOrder\PurchaseOrderItem;
use App\Models\System\Alert;
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

class CustomerItemController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Sản phẩm';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    public function grid()
    {
        $itemController = new ItemController();
        $grid = $itemController->grid();

        $grid->model()->whereCustomerId(Admin::user()->id)->orderBy('id', 'desc');

        $grid->disableTools();
        $grid->disableBatchActions();
        $grid->disableCreateButton();
        $grid->disableExport();
        // $grid->disableFilter();
        $grid->disableActions();
        $grid->paginate(30);
        // $grid->disablePagination();
        // $grid->disablePerPageSelector();

        return $grid;
    }
    
}