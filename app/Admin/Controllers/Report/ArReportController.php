<?php

namespace App\Admin\Controllers\Report;

use App\Models\ArReport\ArReport as ArReportModel;
use App\Models\System\ArReport;
use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Encore\Admin\Layout\Row;
use Encore\Admin\Layout\Column;
use Encore\Admin\Widgets\Box;

class ArReportController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Báo cáo kế toán';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ArReportModel());

        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number + 1));
        });
        $grid->column('number', 'STT');

        $grid->column('title', "Tên báo cáo");

        $grid->disableExport();
        $grid->disableFilter();
        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->disablePagination();
        $grid->disablePerPageSelector();
        $grid->paginate(100);

        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new ArReportModel);

        $form->text('title', "Tiêu đề")->rules(['required']);
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });

        return $form;
    }

    public function showRebuild($id, Content $content)
    {
        $report = ArReportModel::find($id);
        return $content->header($report->title)
            ->description('Chi tiết')
            ->row(function (Row $row) use ($id) {
                $row->column(12, function (Column $column) use ($id) {
                    $column->append((new Box('Thông tin danh mục', $this->category($id))));
                });
                $row->column(12, function (Column $column) use ($id) {
                    $column->append((new Box('Chi tiết báo cáo', $this->detail($id))));
                });
            });
    }

    public function category($id)
    {
        $indexCategory = route('admin.ar_category.index') . "?ar_report_id=" . $id;
        $html = '<a href="' . $indexCategory . '" class="btn btn-xs btn-primary">1. Danh sách danh mục</a> &nbsp;';
        $html .= '<a href="" class="btn btn-xs btn-warning">2. Danh sách đơn vị</a> &nbsp;';
        $html .= '<a href="" class="btn btn-xs btn-danger">3. Đồng bộ danh mục + đơn vị</a> &nbsp;';

        return $html;
    }

    public function listCategory()
    {
    }

    public function detail($id)
    {
    }
}
