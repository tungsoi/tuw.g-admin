<?php

namespace App\Admin\Controllers\Report;

use App\Admin\Services\UserService;
use App\Models\Setting\RoleUser;
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
        $grid->model()->selectRaw('DATE(created_at) as created_at')->groupBy(DB::RAW('DATE(created_at)'))->orderBy('created_at', 'desc');
        // ->whereIn('id', function($query){
        //     $query->select(DB::RAW('max(id)'))
        //     ->from('transactions')
        //     ->whereNotNull('bank_id')
        //     ->where('type_recharge', 1)
        //     ->groupBy(DB::RAW('DATE(created_at)'));
        // })
        // ->orderBy('id', 'desc');
        $grid->disableFilter();

        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT')->style('text-align: center');
        $grid->created_at('Ngày')->display(function () {
            return date('d-m-Y', strtotime($this->created_at));
        });

        $service = new UserService();
        $banks = $service->GetListBankAccount();
        $userIdsArRole = RoleUser::whereRoleId(5)->pluck('user_id');

        foreach ($banks as $bank_id => $bank_name) {
            $grid->column('bank_'.$bank_id, $bank_name)->display(function () use ($bank_id, $userIdsArRole) {
                $money = Transaction::select('money')
                ->where('created_at', 'like', date("Y-m-d", strtotime($this->created_at)).'%')
                ->whereIn('user_id_created', $userIdsArRole)
                ->where('type_recharge', 1)
                ->where('bank_id', $bank_id)
                ->get();

                $date = Carbon::parse($this->created_at);

                $route = route('admin.transactions.index') ."?content=&"
                . "type_recharge%5B%5D=1"
                . "&user_id_created%5B%5D="
                . implode("&user_id_created%5B%5D=", $userIdsArRole->toArray())
                . "&created_at%5Bstart%5D=".date('Y-m-d', strtotime($this->created_at))."&created_at%5Bend%5D=".date('Y-m-d', strtotime($date->addDays(1)))
                . "&bank_id=".$bank_id;
                
                return "<span style='text-align: center'>".number_format($money->sum('money')) . " VND </span> &nbsp; &nbsp; - &nbsp; &nbsp;"
                ."<a href='".$route."' target='_blank'>".$money->count()." giao dịch</a>";
            })->style('text-align: center');
        }

        $grid->column('Tiền mặt')->display(function () use ($userIdsArRole) {
            $money = Transaction::select('money')
            ->where('created_at', 'like', date("Y-m-d", strtotime($this->created_at)).'%')
            ->whereIn('user_id_created', $userIdsArRole)
            ->where('type_recharge', 0)
            ->sum('money');
            
                // dd($money->sum('money'));
            return "<h5 style='text-align: center'>".number_format($money) . " VND </h5>";
                // . "<span>(".$money->count()." giao dịch)</span>";
        })->style('text-align: center');

        $grid->disableActions();
        $grid->disableCreateButton();
        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->disableExport();

        return $grid;
    }
}