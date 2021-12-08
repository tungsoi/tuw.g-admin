<?php

namespace App\Admin\Controllers\ReportWarehouse;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\ReportWarehouse\ReportWarehouse;
use App\Models\System\TransportLine;
use App\Models\System\Warehouse;
use Encore\Admin\Facades\Admin;
use Illuminate\Foundation\Console\Presets\React;
use Illuminate\Http\Request;

class DetailController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title;

    public function __construct()
    {
        $this->title = 'NHẬP KG ĐẦU VÀO ALILOGI';
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ReportWarehouse());
        $grid->model()->orderBy('date', 'desc')
        ->orderBy('title', 'desc')
        ->orderBy('id', 'desc');

        $grid->filter(function($filter) {
            $filter->expand();
            $filter->disableIdFilter();
            $filter->date('date', "Ngày về kho")->date();
            $filter->like('title', "Ký hiệu");
        });

        $grid->column('date',"Ngày về kho")->width(150)->editable();
        $grid->column('order',"STT")->width(64)->editable();
        $grid->column('title', "Ký hiệu")->width(138)->editable();
        $grid->column('weight',"Cân nặng")->width(121)->editable()->totalRow(function ($amount) {
            return number_format($amount);
        });
        $grid->column('lenght','Dài (cm)')->width(150)->editable();
        $grid->column('width','Rộng (cm)')->width(150)->editable();
        $grid->column('height','Cao (cm)')->width(150)->editable();
        $grid->column('cublic_meter', 'Mét khối')->width(150)->display(function () {
            return str_replace('.0000','', $this->cublic_meter);
        })->editable()->totalRow(function ($amount) {
            return number_format($amount, 4);
        });
        $grid->column('line', 'Quy cách đóng gói')->width(100)->editable('select', ReportWarehouse::LINE);
        $grid->transport_route('Line vận chuyển')->editable('select', TransportLine::all()->pluck('code', 'id'));
        $grid->warehouse()->name('Kho nhận hàng');
        $grid->note('Ghi chú')->editable();
        $grid->created_at(trans('admin.created_at'))->display(function () {
            return date('H:i | d-m-Y', strtotime($this->created_at));
        });

        // setup
        $grid->paginate(20);
        $grid->disableActions();

        $grid->disableCreateButton();
        $grid->tools(function ($tools) {
            $tools->append('<div class="btn-group pull-right grid-create-btn" style="margin-right: 10px">
                <a href="" id="btn-add" class="btn btn-sm btn-success" title="Thêm mới">
                    <i class="fa fa-plus"></i><span class="hidden-xs">&nbsp;&nbsp;Thêm mới</span>
                </a>
            </div>');
        });

        $route = route('admin.report_warehouses.create');
        // script
        Admin::script(
            <<<EOT

            $('tfoot').each(function () {
                $(this).insertAfter($(this).siblings('thead'));
            });

            $('#btn-add').on('click', function () {
                window.location.href = '{$route}';
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
