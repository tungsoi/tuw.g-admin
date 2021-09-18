<?php

namespace App\Admin\Controllers\Report;

use App\Models\Setting\RoleUser;
use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SaleReportController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Báo cáo kinh doanh';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User());
        $grid->model()->where("id", '-1');

        $grid->header(function () {
            $ids = RoleUser::whereRoleId(3)->pluck('user_id');
            $users = User::select('id', 'name', 'avatar')->whereIn('id', $ids)->get()->toArray();
            $top = array_slice($users, 0, 3);
            $other = array_slice($users, 3, sizeof($users));
            $normal = array_slice($users, 3, sizeof($users)-3);
            $bottom = array_slice($users, sizeof($users)-3, sizeof($users));

            return view('admin.system.report.sale_report', compact('users', 'top', 'other', 'normal', 'bottom'))->render();
        }); 

        $grid->disableActions();
        $grid->disableBatchActions();
        $grid->disableCreateButton();
        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->disableFilter();
        $grid->disableColumnSelector();
        $grid->disableDefineEmptyPage();
        $grid->disablePagination();

        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        //
    }
}
