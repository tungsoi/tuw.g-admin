<?php

namespace App\Admin\Controllers\ReportWarehouse;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\ReportWarehouse\ReportWarehousePortal;
use Encore\Admin\Facades\Admin;
Use Encore\Admin\Widgets\Table;

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
        $grid->model()->orderBy('date', 'desc')->with('transportCode');

        $grid->filter(function($filter) {
            $filter->expand();
            $filter->disableIdFilter();
            $filter->date('date', "Ngày đầu về kho")->date();
            $filter->like('title', "Ký hiệu");
            $filter->equal('status', "Trạng thái")->select([
                1   =>  'Chưa về đủ',
                2   =>  'Đã xong'
            ]);
        });
        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });

        $grid->column('number', 'STT');
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
        })->totalRow();
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
        
        $states = [
            'on'  => ['value' => 2, 'text' => 'Xong', 'color' => 'success'],
            'off' => ['value' => 1, 'text' => 'Chưa đủ', 'color' => 'danger'],
        ];

        $grid->column('status', 'Trạng thái')->switch($states)->style('text-align: center');

        $grid->created_at(trans('admin.created_at'))->display(function () {
            return date('H:i | d-m-Y', strtotime($this->created_at));
        });
        $grid->updated_at('Cập nhật cuối cùng')->display(function () {
            return date('H:i | d-m-Y', strtotime($this->updated_at));
        });
        $grid->id('Mã vận đơn')->display(function (){
            return $this->transportCode->count();
        })->expand(function ($model) {
            $data = $this->transportCode;

            $temp = [];
            foreach ($data as $key =>  $row) {
                $temp[] = [
                    $key+1,
                    $row->transport_code,
                    $row->customer_code_input
                ];
            }
        
            return new Table(['STT', 'Mã vận đơn', 'Tên khách hàng'], $temp);
        })->style('width: 100px; text-align: center;');

        // setup
        $grid->paginate(20);

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
        $form->text('title', "Ký hiệu");
        $form->text('count', 'Thực nhận');
        $form->text('weight',"Cân nặng");
        $form->text('cublic_meter', 'Mét khối');
        $form->text('note', 'Ghi Chú');

        $states = [
            'on'  => ['value' => 2, 'text' => 'Xong', 'color' => 'success'],
            'off' => ['value' => 1, 'text' => 'Chưa đủ', 'color' => 'danger'],
        ];
        $form->switch('status', 'Trạng thái')->states($states)->default(1);

        $form->disableEditingCheck();
        $form->disableCreatingCheck();
        $form->disableViewCheck();

        return $form;
    }
}
