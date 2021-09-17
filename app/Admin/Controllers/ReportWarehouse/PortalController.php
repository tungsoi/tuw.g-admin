<?php

namespace App\Admin\Controllers\ReportWarehouse;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\ReportWarehouse\ReportWarehousePortal;
use Encore\Admin\Facades\Admin;

class PortalController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title;

    public function __construct()
    {
        $this->title = 'BÁO CÁO NHẬP KHO THEO MÃ LÔ HÀNG';
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ReportWarehousePortal());
        $grid->model()->orderBy('date', 'desc');

        $grid->filter(function($filter) {
            $filter->expand();
            $filter->disableIdFilter();
            $filter->date('date', "Ngày đầu về kho")->date();
            $filter->like('title', "Ký hiệu");
        });

        $grid->column('date',"Ngày đầu về kho");
        $grid->column('title', "Ký hiệu");
        $grid->column('count', 'Thực nhận')->display(function () {
            $arr = explode('-', $this->title);
            if (sizeof($arr) == 2) {
                if ($arr[1] == $this->count) {
                    return $this->count;
                }

                else {
                    return '<span class="label label-danger">'.$this->count.'</span>';
                }
            }

            return $this->count;
        });
        $grid->column('weight',"Cân nặng")->display(function () {
            return number_format($this->weight, 2);
        })->totalRow();
        $grid->column('cublic_meter', 'Mét khối')->display(function () {
            $number = number_format($this->cublic_meter, 2);
            if ($number == '0.00') {
                return null;
            }
            return $number;
        })->totalRow();
        // $grid->column('line', 'Line');
        $grid->column('note', 'Ghi Chú')->editable();
        $grid->created_at(trans('admin.created_at'))->display(function () {
            return date('H:i | d-m-Y', strtotime($this->created_at));
        });
        $grid->updated_at('Cập nhật cuối cùng')->display(function () {
            return date('H:i | d-m-Y', strtotime($this->updated_at));
        });

        // setup
        $grid->paginate(200);

        // script
        Admin::script(
            <<<EOT

            $('tfoot').each(function () {
                $(this).insertAfter($(this).siblings('thead'));
            });
EOT
    );

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
        $show = new Show(ReportWarehousePortal::findOrFail($id));

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
        $form = new Form(new ReportWarehousePortal);

        $form->display('id', __('ID'));
        $form->date('date', "Ngày về kho")->default(now());
        $form->text('order', "STT");
        $form->text('title', "Ký hiệu");
        $form->text('weight', "Cân nặng");
        $form->text('lenght', 'Dài (cm)');
        $form->text('width', 'Rộng (cm)');
        $form->text('height', 'Cao (cm)');
        $form->text('cublic_meter', 'Mét khối');
        $form->text('line', 'Line');
        $form->text('note', 'Ghi chú');

        $form->disableEditingCheck();
        $form->disableCreatingCheck();
        $form->disableViewCheck();

        return $form;
    }
}
