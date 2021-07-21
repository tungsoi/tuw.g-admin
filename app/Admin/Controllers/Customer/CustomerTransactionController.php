<?php

namespace App\Admin\Controllers\Customer;

use App\Admin\Services\UserService;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\OrderItem;
use App\Models\PurchaseOrder\PurchaseOrderItem;
use App\Models\PurchaseOrder\PurchaseOrderItemStatus;
use App\Models\System\ExchangeRate;
use App\Models\System\Transaction;
use App\Models\System\Warehouse;
use App\User;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;

class CustomerTransactionController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title;

    public function __construct()
    {
        $this->title = trans('Lịch sử giao dịch');
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Transaction());
        $grid->model()->whereCustomerId(0)->where('money', '!=', 0)->orderBy('id', 'desc');

        $id = Admin::user()->id;

        $grid->header(function () use ($id) {

            $customer = User::select('id', 'symbol_name', 'wallet')->whereId($id)->first();
            $empty = true;
            $service = new UserService();
            $data = $service->GetCustomerTransactionHistory($id);

            $mode = "";
            $form = "";
            $transactionId = "";
            if (isset($_GET['mode']) && $_GET['mode'] == 'recharge') {
                $mode = 'recharge';

                if (isset($_GET['transaction_id']) && $_GET['transaction_id'] != "") { 
                    $transactionId = $_GET['transaction_id'];
                    $flag = Transaction::find($transactionId);

                    if ($flag) {
                        $form = $this->formRecharge($id, $transactionId)->edit($transactionId)->render();
                    } else {
                        $form = $this->formRecharge($id, "")->render();
                    }
                } else {
                    $form = $this->formRecharge($id, "")->render();
                }
            }

            $disableAction = true;
            return view('admin.system.customer.transaction', compact('customer', 'empty', 'data', 'mode', 'form', 'transactionId', 'disableAction'))->render();

        });
        
        $grid->disableFilter();
        $grid->disableExport();
        $grid->disableCreateButton();
        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->paginate(1000);
        $grid->disableColumnSelector();
        $grid->disablePagination();

        return $grid;
    }
}
