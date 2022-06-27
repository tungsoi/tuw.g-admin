<?php

namespace App\Admin\Controllers\ReportWarehouse;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\ReportWarehouse\ReportWarehouse;
use App\Models\ReportWarehouse\ReportWarehousePortal;
use App\Models\System\TransportLine;
use App\Models\System\Warehouse;
use Encore\Admin\Facades\Admin;
use Illuminate\Foundation\Console\Presets\React;
use Illuminate\Http\Request;

class DateReportController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title;

    public function __construct()
    {
        $this->title = 'Thống kê nhập kho theo từng ngày';
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ReportWarehouse());

        if (! isset($_GET['date'])) {
            $date = date('Y-m-d');
        } else {
            $date = $_GET['date'];
        }

        $ids = ReportWarehouse::where('date', $date)->get()->unique('title')->pluck('id');

        $grid->model()->whereIn('id', $ids)->orderBy('id', 'desc');

        $grid->filter(function($filter) {
            $filter->expand();
            $filter->disableIdFilter();
            $filter->like('title', "Ký hiệu");
            $filter->equal('date', 'Ngày về kho')->date()->default(date('Y-m-d'));
        });

        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });

        $grid->column('number', 'STT');
        $grid->column('date',"Ngày về kho")->width(150);
        $grid->column('title', "Ký hiệu")->width(138);
        $grid->column('created_at', 'Thực nhận')->display(function () use ($date) {
            return ReportWarehouse::where('date', $date)->whereTitle($this->title)->count();
        })->totalRow(function ($number) {
            return "<span id='row_total_count'></span>";
        });
        $grid->column('updated_at', 'Tổng kg')->display(function () use ($date) {
            return ReportWarehouse::where('date', $date)->whereTitle($this->title)->sum('weight');
        })->totalRow(function ($number) {
            return "<span id='row_total_kg'></span>";
        });
        $grid->column('id', 'Tổng khối')->display(function () use ($date) {
            return ReportWarehouse::where('date', $date)->whereTitle($this->title)->sum('cublic_meter');
        })->totalRow(function ($number) {
            return "<span id='row_total_m3'></span>";
        });

        // setup
        $grid->paginate(200);
        $grid->disableActions();
        $grid->disableBatchActions();
        $grid->disableCreateButton();
        $grid->disableExport();

        Admin::script(
            <<<EOT

            $('tfoot').each(function () {
                $(this).insertAfter($(this).siblings('thead'));
            });

            let column_count = $('tbody .column-created_at');

            let total_count = 0;
            column_count.each( function( i, el ) {
                var elem = $( el );
                let html = parseFloat($.trim(elem.html()));
                total_count += html;
            });
            $('#row_total_count').html(total_count);

            let column_kg = $('tbody .column-updated_at');

            let total_kg = parseFloat(0.0);
            column_kg.each( function( i, el ) {
                var elem = $( el );
                let html = parseFloat($.trim(elem.html()));
                total_kg += html;
            });
            $('#row_total_kg').html(total_kg.toFixed(2));


            let column_m3 = $('tbody .column-id');

            let total_m3 = parseFloat(0.0);
            column_m3.each( function( i, el ) {
                var elem = $( el );
                let html = parseFloat($.trim(elem.html()));
                total_m3 += html;
            });
            $('#row_total_m3').html(total_m3.toFixed(2));
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
        $form->setAction(route('admin.report_warehouses.storeDetail'));

        $form->display('id', __('ID'));
        $form->date('date', "Ngày về kho")->default(now());
        $form->text('title', "Ký hiệu");
        $form->select('transport_route', 'Line vận chuyển')
        ->options(TransportLine::all()->pluck('code', 'id'))
        ->default(1);
        $form->select('warehouse_id', 'Kho nhận hàng')
        ->options(Warehouse::all()->pluck('name', 'id'))
        ->default(2);

        $line = ReportWarehouse::LINE;

        $form->html(function () use ($line) {
            return view('admin.system.transport_order.report-warehouse-template', compact('line'))->render();
        });

        $form->html(function () use ($line) {
            return view('admin.report-warehouse', compact('line'))->render();
        });

        $form->disableEditingCheck();
        $form->disableCreatingCheck();
        $form->disableViewCheck();
        $form->disableSubmit();
        $form->disableReset();

        // callback before save

        return $form;
    }

    public function storeDetail(Request $request) {
        $data = $request->all();

        unset($data['order'][0]);
        unset($data['weight'][0]);
        unset($data['lenght'][0]);
        unset($data['width'][0]);
        unset($data['height'][0]);
        unset($data['line'][0]);
        unset($data['note'][0]);

        $size = sizeof($data['order']);

        for ($i = 1; $i <= $size; $i++) {
            if (strlen($data['order'][$i]) == 1) {
                $order = str_pad($data['order'][$i], 2, '0', STR_PAD_LEFT);
            }
            else {
                $order = $data['order'][$i];
            }

            $res = [
                'date'  =>  $data['date'],
                'order' =>  $order,
                'title' =>  $data['title'],
                'weight'    =>  $data['weight'][$i],
                'lenght'    =>  $data['lenght'][$i],
                'width'    =>  $data['width'][$i],
                'height'    =>  $data['height'][$i],
                'cublic_meter'    => number_format( ($data['lenght'][$i]*$data['width'][$i]*$data['height'][$i]) / 1000000, 4, '.', ''),
                'line'    =>  $data['line'][$i],
                'transport_route'   =>  $data['transport_route'],
                'warehouse_id'  =>  $data['warehouse_id'],
                'note'  =>  $data['note'][$i]
            ];

            ReportWarehouse::firstOrCreate($res);
        }

        admin_toastr('Lưu thành công', 'success');
        return redirect()->route('admin.report_warehouses.index');
    }

    public function updateDetail(Request $request) {
        ReportWarehouse::find($request->pk)->update([
            $request->name  =>  $request->value
        ]);

        $row = ReportWarehouse::find($request->pk);
        $cublic_meter = number_format(($row->lenght * $row->width * $row->height) / 1000000, 4, '.', '');
        $row->cublic_meter = $cublic_meter;
        $row->save();

        return response()->json([
            'status' =>  true,
            'message'   =>  'Lưu thành công test'
        ]);

    }


}
