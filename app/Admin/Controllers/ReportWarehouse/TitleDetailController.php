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

class TitleDetailController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title;

    public function __construct()
    {
        $this->title = 'Chi tiết từng mã lô theo từng ngày nhập kho';
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ReportWarehousePortal());
        $grid->model()->orderBy('id', 'desc');

        $grid->filter(function($filter) {
            $filter->expand();
            $filter->disableIdFilter();
            $filter->like('title', "Ký hiệu");
        });

        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });

        $grid->column('number', 'STT');
        $grid->column('date',"Ngày về kho")->width(150);
        $grid->column('title', "Ký hiệu")->width(138);
        $grid->detail('Chi tiết')->display(function () {
            $title = $this->title;
            $date = ReportWarehouse::select('date')->whereTitle($title)->groupBy('date')->pluck('date')->toArray();

            $data = [];
            foreach ($date as $date_row) {
                $raw = ReportWarehouse::whereTitle($title)->where('date', $date_row)->get();
                $temp[$date_row] = [
                    'count' =>  $raw->count(),
                    'kg'    =>  $raw->sum('weight'),
                    'm3'    =>  $raw->sum('cublic_meter')
                ];
            }

            return view('admin.system.report_warehouse.detail', compact('date', 'temp'));
        });

        // setup
        $grid->paginate(20);
        $grid->disableActions();
        $grid->disableBatchActions();
        $grid->disableCreateButton();

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
