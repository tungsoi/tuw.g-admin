<?php

namespace App\Admin\Controllers\System;

use App\Models\Setting\RoleUser;
use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\System\Service;
use Illuminate\Support\Facades\DB;

class ServiceController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title;

    public function __construct()
    {
        $this->title = 'Service';
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Service);

        $grid->id('STT');
        $grid->title('Tiêu đề');
        $grid->description('Chi tiết');
        $grid->column('image', 'Ảnh')->lightbox(['width' => 40])->width(60);
        $grid->disableExport();
        $grid->disableFilter();
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
        $show = new Show(Service::findOrFail($id));

        $show->field('id', trans('admin.id'));
        $show->title(trans('admin.title'));
        $show->description('Chi tiết');
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
        $form = new Form(new Service);

        $form->display('id', __('ID'));
        $form->text('title', 'Tiêu đề')->rules('required');
        $form->textarea('description', 'Chi tiết')->rules('required');
        $form->image('image', 'Ảnh')
            ->rules('mimes:jpeg,png,jpg')
            ->help('Ảnh đầu tiên sẽ hiển thị là ảnh đại diện')
            ->removable()->rules('required');
        $form->disableEditingCheck();
        $form->disableCreatingCheck();
        $form->disableViewCheck();

        return $form;
    }
}
