<?php

namespace App\Admin\Controllers\Report;

use App\Admin\Services\UserService;
use App\Models\System\Transaction;
use App\User;
use Carbon\Carbon;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Illuminate\Support\Facades\DB;

class BankingCheckController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title;

    public function __construct()
    {
        $this->title = 'Báo cáo nạp tiền tài khoản';
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Transaction());
        $grid->model()
        ->whereIn('id', function($query){
            $query->select(DB::RAW('max(id)'))
            ->from('transactions')
            ->whereNotNull('bank_id')
            ->where('type_recharge', 1)
            ->groupBy(DB::RAW('DATE(created_at)'));
        })
        ->orderBy('id', 'desc');
        $grid->disableFilter();

        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT')->style('text-align: center');
        $grid->created_at('Ngày')->display(function () {
            return date('Y-m-d', strtotime($this->created_at)) ?? "";
        });

        $service = new UserService();
        $banks = $service->GetListBankAccount();

        foreach ($banks as $bank_id => $bank_name) {
            $grid->column('bank_'.$bank_id, $bank_name)->display(function () use ($bank_id) {
                $money = Transaction::where('type_recharge', 1)
                    ->whereBankId($bank_id)
                    ->where('created_at', 'like', date('Y-m-d', strtotime($this->created_at))."%")
                    ->get();
                
                return "<h5 style='text-align: center'>".number_format($money->sum('money')) . " VND </h5>"
                    . "<span>(".$money->count()." giao dịch)</span>";
            })->style('text-align: center');
        }

        $grid->disableActions();
        $grid->disableCreateButton();
        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->disableExport();

        return $grid;
    }
}