<?php

namespace App\Admin\Controllers\System;

use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\System\TeamSale;
use Illuminate\Support\Facades\DB;

class TeamSaleController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title;

    public function __construct()
    {
        $this->title = 'Team Sale';
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new TeamSale);

        $grid->filter(function($filter) {
            $filter->expand();
            $filter->disableIdFilter();
            $filter->like('title', trans('admin.title'));
        });

        $grid->id('STT');
        $grid->name('Tên nhóm');
        $grid->leader('Leader Team')->display(function ($e)
        {
            return $this->leaderStaff->name ?? "";
        });
        $grid->members('Thành viên')->display(function ()
        {
            $users = User::whereIn('id', $this->members)->get()->pluck('name');

            return $users;
        })->label()->width(500);

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
        $show = new Show(TeamSale::findOrFail($id));

        $show->field('id', trans('admin.id'));
        $show->title(trans('admin.title'));
        $show->order(trans('admin.order'));
        $show->field('created_at', trans('admin.created_at'));
        $show->field('updated_at', trans('admin.updated_at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new TeamSale);

        $form->display('id', __('ID'));
        $form->text('name', 'Tên nhóm')->rules('required');

        $sale_users = DB::connection('aloorder')->table('admin_role_users')->where('role_id',3)->pluck('user_id');
        $form->select('leader', 'Leader Team')->options(User::whereIn('id', $sale_users)->whereIsActive(1)->pluck('name', 'id'));

        $members = User::whereIn('id', $sale_users)->whereIsActive(1)->pluck('name', 'id');
        $form->listbox('members', 'Thành viên')->options($members);

        $form->disableEditingCheck();
        $form->disableCreatingCheck();
        $form->disableViewCheck();

        return $form;
    }
}
