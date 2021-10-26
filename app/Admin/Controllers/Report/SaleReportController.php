<?php

namespace App\Admin\Controllers\Report;

// use App\Admin\Actions\Exporter\SaleReportExporter;
// use App\Models\ReportDetailBackup;

use App\Admin\Services\UserService;
use App\Models\SaleReport\Report;
use App\Models\SaleReport\ReportDetail;
use App\Models\System\TeamSale as SystemTeamSale;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\User;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\DB;

class SaleReportController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title;

    public function __construct()
    {
        $this->title = 'BÁO CÁO KINH DOANH';
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Report());
        $grid->model()->orderBy('order', 'desc');

        $grid->filter(function($filter) {
            $filter->expand();
            $filter->disableIdFilter();
        });
        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT');
        $grid->title(trans('admin.title'));
        $grid->begin_date('Ngày bắt đầu')->display(function () {
            return date('d-m-Y', strtotime($this->begin_date));
        });
        $grid->finish_date('Ngày kết thúc')->display(function () {
            return date('d-m-Y', strtotime($this->finish_date));
        });

        $grid->updated_at('Thời gian cập nhật cuối cùng')->display(function () {
            return date('H:i | d-m-Y', strtotime($this->updated_at));
        });

        $grid->disableBatchActions();
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            // $actions->disableView();
            $actions->disableEdit();
            $route = route('admin.revenue_reports.show', $actions->getkey());
            $detech = route('admin.revenue_reports.detech', $actions->getkey());
            $portal = "false";
            if ($this->row->id == 5) {
                $portal = "true";
            }
            $actions->append('<a href="'.$route.'?mode=new&portal='.$portal.'" class="grid-row-view btn btn-xs btn-warning" data-toggle="tooltip" title="" data-original-title="Xem chi tiết">
                    <i class="fa fa-times"></i>
                </a>');

            if (Admin::user()->isRole('administrator') || Admin::user()->isRole('ar_employee')) {
                $actions->append('<a href="'.$detech.'" class="grid-row-view btn btn-xs btn-success" data-toggle="tooltip" title="" data-original-title="Hiệu quả công việc">
                    <i class="fa fa-check"></i>
                </a>');
            }
        });

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
        $grid = new Grid(new ReportDetail());
        $grid->model()->where('sale_report_id', $id);
        
        $report = Report::find($id);

        $grid->filter(function($filter) {
            $filter->expand();
            $filter->disableIdFilter();
            $filter->column(1/2, function ($filter) {
                $filter->where(function ($query) {
                    //
                }, 'Team Sale', 'team_sale')->select(SystemTeamSale::all()->pluck('name', 'id'));
            });
            
            $filter->column(1/2, function ($filter) {
                $service = new UserService();
    
                $filter->where(function ($query) {
                    //
                }, 'Nhân viên', 'user_id')->select($service->GetListSaleEmployee());
            });
           
        });

        if (isset($_GET['mode']) && $_GET['mode'] == 'new')
        {
            $grid->header(function () use ($report) {
                $data = [
                    'begin_date'    =>  $report->begin_date,
                    'finish_date'   =>  $report->finish_date,
                    'created_at'    =>  $report->created_at,
                    'updated_at'    =>  $report->updated_at,
                    'status'        =>  $report->status
                ];
                $detail = ReportDetail::where('sale_report_id', $report->id)->orderBy(DB::raw("`success_order_payment` + `processing_order_payment`"), 'desc');

                if (isset($_GET['team_sale']) && $_GET['team_sale'] != "") {
                    $ids = SystemTeamSale::find($_GET['team_sale'])->members;
                    $detail->whereIn('user_id', $ids);
                }

                if (isset($_GET['user_id']) && $_GET['user_id'] != "") {
                    $detail->where('user_id', $_GET['user_id']);
                }

                if (Admin::user()->isRole('sale_manager')) {

                } else {
                
                    $flag = SystemTeamSale::where('leader', Admin::user()->id)->first();

                    if (Admin::user()->isRole('sale_employee')) {
                        if ($flag != "" && $flag->count() > 0) {
                            $members = $flag->members;
                            $detail->whereIn('user_id', $members);
                        } else {
                            $detail->where('user_id', Admin::user()->id);
                        }
                    }
                }

                $detail = $detail->get();
                $portal = isset($_GET['portal']) ? $_GET['portal'] : "false";

                return view('admin.salereport.header', compact('data', 'report', 'detail', 'portal'));
            });
        }
        else {
            $grid->rows(function (Grid\Row $row) {
                $row->column('number', ($row->number+1));
            });
            $grid->column('number', 'STT');
            $grid->user_id('NHÂN VIÊN KINH DOANH')->display(function () {
                $html = User::find($this->user_id)->name;
                $html .= "<br> " . User::find($this->user_id)->created_at;

                return $html;
            });
            $grid->column('total_customer', 'TỔNG SỐ KHÁCH HÀNG')->display(function () use ($id) {
                $number = $this->total_customer;
                $params = '?user_id='.$this->user_id.'&report_id='.$id;
                $route = route('admin.detail_report.total_customer').$params;

                return "<a target='_blank' href='".$route."'>".$number."</a>";
            });
            $grid->column('new_customer', 'TỔNG SỐ KHÁCH HÀNG MỚI')->totalRow()
            ->display(function () use ($id) {
                $number = $this->new_customer;
                $params = '?user_id='.$this->user_id.'&report_id='.$id;
                $route = route('admin.detail_report.new_customer').$params;

                return "<a target='_blank' href='".$route."'>".$number."</a>";
            });
            $grid->success_order('TỔNG ĐƠN HOÀN THÀNH')->totalRow()
            ->display(function () use ($id) {
                $number = $this->success_order;
                $params = '?user_id='.$this->user_id.'&report_id='.$id;
                $route = route('admin.detail_report.success_order').$params;

                return "<a target='_blank' href='".$route."'>".$number."</a>";
            });
            $grid->success_order_payment('DOANH SỐ (VND)')->totalRow()->display(function () {
                return number_format($this->success_order_payment);
            })->totalRow(function ($amount) {
                return number_format($amount);
            });
            $grid->success_order_payment_new_customers('DOANH SỐ KHÁCH HÀNG MỚI (VND)')->totalRow()->display(function () {
                return number_format($this->success_order_payment_new_customers);
            })->totalRow(function ($amount) {
                return number_format($amount);
            });
            $grid->processing_order('TỔNG ĐƠN CHƯA HOÀN THÀNH')->totalRow()
            ->display(function () use ($id) {
                $number = $this->processing_order;
                $params = '?user_id='.$this->user_id.'&report_id='.$id;
                $route = route('admin.detail_report.processing_order').$params;

                return "<a target='_blank' href='".$route."'>".$number."</a>";
            });
            $grid->processing_order_payment('DOANH SỐ (VND)')->totalRow()->display(function () {
                return number_format($this->processing_order_payment);
            })->totalRow(function ($amount) {
                return number_format($amount);
            });
            $grid->processing_order_payment_new_customers('DOANH SỐ KHÁCH HÀNG MỚI (VND)')->totalRow()->display(function () {
                return number_format($this->processing_order_payment_new_customers);
            })->totalRow(function ($amount) {
                return number_format($amount);
            });
            $grid->owed_processing_order_payment('CÔNG NỢ TRÊN ĐƠN CHƯA HOÀN THÀNH (VND)')->totalRow()
            ->display(function () {
                return number_format($this->owed_processing_order_payment);
            })->totalRow(function ($amount) {
                return number_format($amount);
            });
            $grid->order_service_fee('TỔNG PHÍ DỊCH VỤ (VND)')->display(function () {
                return number_format($this->order_service_fee);
            })->totalRow(function ($amount) {
                return number_format($amount);
            });
            $grid->total_transport_weight('TỔNG KG')->display(function () use ($id) {
                $number = number_format($this->total_transport_weight, 2);
                $params = '?user_id='.$this->user_id.'&report_id='.$id;
                $route = route('admin.detail_report.total_transport_weight').$params;

                return "<a target='_blank' href='".$route."'>".$number."</a>";
            })->totalRow();
            $grid->total_transport_cublic_meter('TỔNG M3')->display(function () {
                return number_format($this->total_transport_cublic_meter, 3);
            })->totalRow();
            $grid->total_transport_fee('DOANH THU PHÍ VẬN CHUYỂN (VND)')->display(function () {
                return number_format($this->total_transport_fee);
            })->totalRow(function ($amount) {
                return number_format($amount);
            });
            $grid->total_customer_wallet('CÔNG NỢ KHÁCH HÀNG (VND)')->display(function () {
                return number_format($this->total_customer_wallet);
            })->totalRow(function ($amount) {
                return number_format($amount);
            });
        }
        

        // export
        // $grid->exporter(new SaleReportExporter());

        // setup
        $grid->disableCreateButton();
        $grid->disableColumnSelector();
        $grid->disableActions();
        $grid->disableBatchActions();
        $grid->paginate(100);

        // style
        Admin::style('
            .modal-lg {
                width: 80% !important;
            }
            .box-body {
                padding: 10px !important;
                font-size: 12px;
            }
        ');

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
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new ReportDetail);

        $form->text('salary');

        $form->disableEditingCheck();
        $form->disableCreatingCheck();
        $form->disableViewCheck();

        return $form;
    }

    public function fetch($id)
    {
        $grid = new Grid(new Report);
        $grid->model()->orderBy('order', 'desc');

        $grid->filter(function($filter) {
            $filter->expand();
            $filter->disableIdFilter();
        });
        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT');
        $grid->title(trans('admin.title'));
        $grid->begin_date('Ngày bắt đầu')->display(function () {
            return date('d-m-Y', strtotime($this->begin_date));
        });
        $grid->finish_date('Ngày kết thúc')->display(function () {
            return date('d-m-Y', strtotime($this->finish_date));
        });

        $grid->updated_at('Thời gian cập nhật cuối cùng')->display(function () {
            return date('H:i | d-m-Y', strtotime($this->updated_at));
        });

        $grid->disableBatchActions();
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            // $actions->disableView();
            $actions->disableEdit();
            $route = route('admin.revenue_reports.show', $actions->getKey());
            $actions->append('<a href="'.$route.'?mode=new" class="grid-row-view btn btn-xs btn-warning" data-toggle="tooltip" title="" data-original-title="Xem chi tiết">
                    <i class="fa fa-eye"></i>
                </a>');
        });

        return $grid;
    }

    public function detech($id, Content $content) {
        return $content
            ->title($this->title())
            ->description("Phân tích hiệu quả công việc - Tháng 9")
            ->body($this->detechGrid($id));
    }

    public function detechGrid($id) {
        $grid = new Grid(new ReportDetail());
        $grid->model()->where('sale_report_id', $id)
        ->orderBy(DB::raw("`success_order_payment` + `processing_order_payment`"), 'desc');

        $grid->header(function () use ($id) {
            $html = Report::find($id)->title;
            return "<h3 style='text-transform: uppercase;'>Phân tích hiệu quả trên / " . $html . "</h3>";
        });

        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT');
        $grid->user_id('NHÂN VIÊN KINH DOANH')->display(function () {
            $html = User::find($this->user_id)->name;
            $html .= "<br> " . User::find($this->user_id)->created_at;

            return $html;
        });
        $grid->service_fee('DOANH THU PHÍ DỊCH VỤ')->display(function () {
           return number_format($this->processing_order_service_fee + $this->success_order_service_fee);
        });
        $grid->transport_payment('DOANH THU VẬN TẢI')->display(function () {
            $total = $this->total_transport_fee;
            $person = ($total * 0.1);

            return number_format($person) . " <br> <span style='color:red'>(".number_format($total).")</span> <br> <i> 10% tổng tiền doanh thu vận tải </i>";
        });

        $grid->exchange_rate_payment('DOANH THU TỶ GIÁ')->display(function () {
            $total = $this->success_order_payment_rmb + $this->processing_order_payment_rmb;
            $person = ($total * 30);

            return number_format($person) . " <br> <span style='color:red'>(".number_format($total).")</span> <br> <i> 30 * tổng tiền tệ đơn hàng </i>";
        });
        $grid->total_fee("TỔNG DOANH THU")->display(function () {
            $service_fee = $this->processing_order_service_fee + $this->success_order_service_fee;
            $transport_payment = $this->total_transport_fee * 0.1;
            $exchange_rate_payment = ($this->success_order_payment_rmb + $this->processing_order_payment_rmb) * 30;

            return number_format(
                $service_fee
                + $transport_payment
                + $exchange_rate_payment
            );
        })->label('success');
        $grid->salary("TIỀN LƯƠNG THỰC NHẬN")->display(function () {
            return number_format($this->salary);
        })->editable();

        $grid->dis("HIỆU QUẢ SAU TRỪ LƯƠNG")->display(function () {
            if ($this->salary != 0) {
                $service_fee = $this->processing_order_service_fee + $this->success_order_service_fee;
                $transport_payment = $this->total_transport_fee * 0.1;
                $exchange_rate_payment = ($this->success_order_payment_rmb + $this->processing_order_payment_rmb) * 30;

                $total = (
                    $service_fee
                    + $transport_payment
                    + $exchange_rate_payment
                );

                return number_format($total - $this->salary);
            }

            return "<span style='color:red'>Chưa điền tiền lương</span>";
        });

        $grid->paginate(50);
        $grid->disableBatchActions();
        $grid->disableCreateButton();
        $grid->disableFilter();
        $grid->disableActions();

        Admin::script($this->script());

        return $grid;
    }


    public function script() {
        $route = route('admin.revenue_reports.index');

        return <<<SCRIPT
            $('.column-salary a').each(function () {
                $(this).attr('data-url', "{$route}" + "/" + $(this).attr('data-pk'));
            });

            $(document).on('click', '.editable-submit', function () {
                window.location.reload();
            });
SCRIPT;
    }
}
