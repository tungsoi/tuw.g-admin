<?php

namespace App\Admin\Controllers\ReportWarehouse;

use App\Admin\Actions\Exporter\DailyReportWarehouseExporter;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\ReportWarehouse\ReportWarehouse;
use App\Models\System\TransportLine;
use Encore\Admin\Facades\Admin;

class DailyController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title;

    public function __construct()
    {
        $this->title = 'BÁO CÁO KG NHẬP KHO THEO NGÀY';
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

        $routes = TransportLine::get();

        $grid->column('date',"Ngày")->totalRow(function () {
            return "-";
        });
        $grid->column('total', 'Số lượng')->display(function () use ($routes) {
            
            $html = "<ul style='padding-left: 10px'>";
            $total = 0;
            foreach ($routes as $route) {
                $content = ReportWarehouse::where('date', $this->date)
                ->whereIn('line', [0, 1])
                ->whereTransportRoute($route->id)
                ->count();
                $total += $content;

                $html .= "<li>".$route->code." : ".$content."</li>";
            }
            $html .= "<li>Tổng : <b class='sum_total'>".$total."</b></li>";
            $html .= "</ul>";

            return $html;
        });
        $grid->column('weight', 'Tổng KG')->display(function () use ($routes) {
            $html = "<ul style='padding-left: 10px'>";
            $total = 0;
            foreach ($routes as $route) {
                $content = ReportWarehouse::where('date', $this->date)
                ->whereTransportRoute($route->id)
                ->sum('weight');
                $total += $content;

                $html .= "<li>".$route->code." : ".$content."</li>";
            }
            $html .= "<li>Tổng : <b class='sum_weight'>".$total."</b></li>";
            $html .= "</ul>";

            return $html;
        });
        $grid->column('cublic_meter', 'Tổng M3')->display(function () use ($routes) {
            $html = "<ul style='padding-left: 10px'>";
            $total = 0;
            foreach ($routes as $route) {
                $content = ReportWarehouse::where('date', $this->date)
                ->whereTransportRoute($route->id)
                ->sum('cublic_meter');
                $total += $content;

                $html .= "<li>".$route->code." : ".$content."</li>";
            }
            $html .= "<li>Tổng : <b class='sum_cublic_meter'>".$total."</b></li>";
            $html .= "</ul>";

            return $html;
        });
        $grid->column('box', 'Số bao')->display(function () use ($routes) {
            $html = "<ul style='padding-left: 10px'>";
            $total = 0;
            foreach ($routes as $route) {
                $content = ReportWarehouse::where('date', $this->date)
                ->whereTransportRoute($route->id)
                ->where('line', 0)->count();
                $total += $content;

                $html .= "<li>".$route->code." : ".$content."</li>";
            }
            $html .= "<li>Tổng : <b class='sum_box'>".$total."</b></li>";
            $html .= "</ul>";

            return $html;
        });
        $grid->column('kg_box', 'Số KG bao')->display(function () use ($routes) {
            $html = "<ul style='padding-left: 10px'>";
            $total = 0;
            foreach ($routes as $route) {
                $content = ReportWarehouse::where('date', $this->date)
                ->whereTransportRoute($route->id)
                ->where('line', 0)
                ->sum('weight');
                $total += $content;

                $html .= "<li>".$route->code." : ".$content."</li>";
            }
            $html .= "<li>Tổng : <b class='sum_kg_box'>".$total."</b></li>";
            $html .= "</ul>";

            return $html;
        });
        $grid->column('m3_box', 'M3 bao')->display(function () use ($routes) {
            $html = "<ul style='padding-left: 10px'>";
            $total = 0;
            foreach ($routes as $route) {
                $content = ReportWarehouse::where('date', $this->date)
                ->whereTransportRoute($route->id)
                ->where('line', 0)
                ->sum('cublic_meter');
                $total += $content;

                $html .= "<li>".$route->code." : ".$content."</li>";
            }
            $html .= "<li>Tổng : <b class='sum_m3_box'>".$total."</b></li>";
            $html .= "</ul>";

            return $html;
        });
        $grid->column('package', 'Số kiện')->display(function () use ($routes) {
            $html = "<ul style='padding-left: 10px'>";
            $total = 0;
            foreach ($routes as $route) {
                $content = ReportWarehouse::where('date', $this->date)
                ->whereTransportRoute($route->id)
                ->where('line', 1)
                ->count();
                $total += $content;

                $html .= "<li>".$route->code." : ".$content."</li>";
            }
            $html .= "<li>Tổng : <b class='sum_package'>".$total."</b></li>";
            $html .= "</ul>";

            return $html;
        });
        $grid->column('kg_package', 'Số KG kiện')->display(function () use ($routes) {
            $html = "<ul style='padding-left: 10px'>";
            $total = 0;
            foreach ($routes as $route) {
                $content = ReportWarehouse::where('date', $this->date)
                ->whereTransportRoute($route->id)
                ->where('line', 1)
                ->sum('weight');
                $total += $content;

                $html .= "<li>".$route->code." : ".$content."</li>";
            }
            $html .= "<li>Tổng : <b class='sum_kg_package'>".$total."</b></li>";
            $html .= "</ul>";

            return $html;
        });
        $grid->column('m3_package', 'M3 kiện')->display(function () use ($routes) {
            $html = "<ul style='padding-left: 10px'>";
            $total = 0;
            foreach ($routes as $route) {
                $content = ReportWarehouse::where('date', $this->date)
                ->whereTransportRoute($route->id)
                ->where('line', 1)
                ->sum('cublic_meter');
                $total += $content;

                $html .= "<li>".$route->code." : ".$content."</li>";
            }
            $html .= "<li>Tổng : <b class='sum_m3_package'>".$total."</b></li>";
            $html .= "</ul>";

            return $html;
        });

        // setup
        $grid->paginate(20);
        $grid->disableActions();

        // $grid->exporter(new DailyReportWarehouseExporter());

        if (isset($_GET['date']) && sizeof($_GET['date']) > 1) {
            Admin::script(
                <<<EOT
    
                $('tfoot').each(function () {
                    $(this).insertAfter($(this).siblings('thead'));
                });
    
                $( document ).ready(function() {
                    $('tfoot .column-date').html("");
    
                    $('tfoot .column-total').html(getTotal('sum_total', 0));
                    $('tfoot .column-weight').html(getTotal('sum_weight', 1));
                    $('tfoot .column-cublic_meter').html(getTotal('sum_cublic_meter'));
                    $('tfoot .column-box').html(getTotal('sum_box', 0));
                    $('tfoot .column-kg_box').html(getTotal('sum_kg_box', 2));
                    $('tfoot .column-m3_box').html(getTotal('sum_m3_box'));
                    $('tfoot .column-package').html(getTotal('sum_package', 0));
                    $('tfoot .column-kg_package').html(getTotal('sum_kg_package', 2));
                    $('tfoot .column-m3_package').html(getTotal('sum_m3_package'));
                });
    
                function getTotal(cl, prefix = 4) {
                    let obj = $('tbody .'+cl);
    
                    let total = 0;
                    obj.each( function( i, el ) {
                        var elem = $( el );
                        let html = parseFloat($.trim(elem.html()));
                        total += html;
                    });
                    total = total.toFixed(prefix);
    
                    return total;
                }
EOT
    );
    }
    else {
        Admin::script(
            <<<EOT

            $('tfoot').each(function () {
                $(this).insertAfter($(this).siblings('thead'));
            });
EOT
);
    }

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
