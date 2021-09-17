<?php

namespace App\Admin\Controllers\ReportWarehouse;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\ReportWarehouse\ReportWarehouse;
use App\Models\System\TransportLine;
use Encore\Admin\Facades\Admin;

class ReportRouteController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title;

    public function __construct()
    {
        $this->title = 'BÁO CÁO KG NHẬP KHO THEO NGÀY, THEO LINE';
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ReportWarehouse());

        $grid->model()->select('date')->groupBy('date')->orderBy('date', 'desc');

        $grid->filter(function($filter) {
            $filter->expand();
            $filter->disableIdFilter();
            $filter->between('date', "Ngày")->date();
        });

        $grid->column('date',"Ngày");
        $grid->column('total', 'Số lượng')->display(function () {
            return ReportWarehouse::where('date', $this->date)->whereIn('line', [0, 1])->count();
        });
        $grid->column('weight', 'Tổng KG')->display(function () {
            return ReportWarehouse::where('date', $this->date)->sum('weight');
        });
        $grid->column('cublic_meter', 'Tổng M3')->display(function () {
            return ReportWarehouse::where('date', $this->date)->sum('cublic_meter');
        });
        $grid->column('box', 'Số bao')->display(function () {
            return ReportWarehouse::where('date', $this->date)->where('line', 0)->count();
        });
        $grid->column('kg_box', 'Số KG bao')->display(function () {
            return ReportWarehouse::where('date', $this->date)->where('line', 0)->sum('weight');
        });
        $grid->column('m3_box', 'M3 bao')->display(function () {
            return ReportWarehouse::where('date', $this->date)->where('line', 0)->sum('cublic_meter');
        });
        $grid->column('package', 'Số kiện')->display(function () {
            return ReportWarehouse::where('date', $this->date)->where('line', 1)->count();
        });
        $grid->column('kg_package', 'Số KG kiện')->display(function () {
            return ReportWarehouse::where('date', $this->date)->where('line', 1)->sum('weight');
        });
        $grid->column('m3_package', 'M3 kiện')->display(function () {
            return ReportWarehouse::where('date', $this->date)->where('line', 1)->sum('cublic_meter');
        });
        $grid->column('transport_route', 'Line vận chuyển')->display(function () {
            $transport_routes = TransportLine::all();
            $html = "<ul style='padding-left: 10px'>";
            foreach ($transport_routes as $route) {
                $html .= "<li>".$route->title."</li>";
            }
            $html .= "</ul>";

            return $html;
        });

        // setup
        $grid->paginate(20);
        $grid->disableActions();

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
        $show = new Show(ReportWarehouse::findOrFail($id));

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
        $form = new Form(new ReportWarehouse);

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

        $form->disableEditingCheck();
        $form->disableCreatingCheck();
        $form->disableViewCheck();

        return $form;
    }
}
