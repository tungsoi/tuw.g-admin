<?php

namespace App\Admin\Controllers\ReportAr;

use App\Models\ArReport\Category;
use App\Models\ArReport\Detail;
use App\Models\ArReport\Unit;
use App\Models\ArReport\Report;
use App\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Table;
use PhpParser\Node\Stmt\Catch_;

class DetailController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Chi tiết tháng';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Report());

        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT');
        $grid->title('Tiêu đề');
        
        $grid->column('created_at', "Ngày tạo")->display(function () {
            return date('H:i | d-m-Y', strtotime($this->created_at));
        })->style('text-align: center; width: 200px');

        $grid->column('updated_at', "Ngày cập nhật")->display(function () {
            return date('H:i | d-m-Y', strtotime($this->updated_at));
        })->style('text-align: center; width: 200px');
        
        $grid->disableExport();
        $grid->disableFilter();
        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->disablePagination();
        $grid->disablePerPageSelector();
        $grid->paginate(100);
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            // $actions->disableView();
        });

        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Report);

        $form->text('title', "Tiêu đề")->rules(['required']);

        $form->saved(function (Form $form) {
            $id = $form->model()->id;

            $categories = Category::all();
            $units = Unit::all();

            foreach ($categories as $key => $category) {
                $temp = [];
                foreach ($units as $unit) {
                    $temp[] = [
                        'unit_id' => $unit->id,
                        'content'   =>  "test " . $unit->id
                    ];
                }

                $data = [
                    'ar_report_id'  =>  $id,
                    'category_id'   =>  $category->id,
                    'content'   =>  json_encode($temp),
                    'note'  =>  "note"
                ];

                if ($key == 0) {
                    $data['unit_id'] = json_encode($units->pluck('id'));
                }
                Detail::create($data);
            }

            return redirect('admin/ars/details/' . $id);
        });

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });

        return $form;
    }

    public function detail($id) {
        $report = Report::findOrFail($id);
        $units = $report->details->first()->unit_id;
        $unit_ids = json_decode($units);
        $units = Unit::whereIn('id', $unit_ids)->get();
        
        $header = ["STT", "CHỈ TIÊU"];
        foreach ($units as $unit) {
            $header[] = $unit->title;
        }
        $header[] = "GHI CHÚ";
        $body = [];

        $details = $report->details;
        $level_1_cat = Category::whereIn('id', $details->pluck('category_id')->toArray())->whereParentId(0)->pluck('id')->toArray();
        $level_1_cat_res = [];
        $level_1_order = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'X', 'Y', 'W', 'J', 'Z'];

        $color = [];
        $level_2_cat_res = [];
        foreach ($level_1_cat as $key => $id) {
            $level_1_cat_res[$level_1_order[$key]] = $id; 

            $level_2_items = Category::whereIn('id', $details->pluck('category_id')->toArray())->where('parent_id', $id)->pluck('id')->toArray();

            if (sizeof($level_2_items) > 0) {
                foreach ($level_2_items as $key => $id_item) {
                    $flag = Category::whereIn('id', $details->pluck('category_id')->toArray())->where('parent_id', $id_item)->pluck('id')->toArray();
                    
                    $level_2_cat_res[$id_item] = $key+1;
                    if (sizeof($flag) > 0) {
                        $color[$id_item] = "rosybrown";
                    }
                }
            } 
        }

        foreach ($details as $key => $detail) {
            $order = $detail->category->id;

            if (in_array($detail->category->id, $level_1_cat_res)) {
                $order = array_search($order, $level_1_cat_res);
                $color[$detail->category->id] = "wheat";
            } else if (isset($level_2_cat_res[$detail->category->id])) {
                $order = $level_2_cat_res[$detail->category->id];
            }
            $body[$detail->category->id] = [
                'order' =>  $order,
                'title' =>  $detail->category->title,
            ];

            $units = json_decode($details->first()->content);
            foreach ($units as $unit_row) {
                $body[$detail->category->id][] = $unit_row->content;
            }

            $body[$detail->category->id]['note'] = $detail->note;
        }

        return view('admin.system.ar_report.detail', compact('report', 'units', 'header', 'body', 'color'));
    }
}
