<?php

namespace App\Admin\Controllers\Report;

// use App\Admin\Actions\Exporter\SaleReportExporter;
// use App\Models\ReportDetailBackup;

use App\Admin\Services\UserService;
use App\Models\SaleReport\Report;
use App\Models\SaleReport\ReportDetail;
use App\Models\SaleReport\SaleSalary;
use App\Models\System\TeamSale as SystemTeamSale;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\User;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\DB;

Use Encore\Admin\Widgets\Table;
use Illuminate\Http\Request;

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

            $route_salary = route('admin.revenue_reports.salary', $actions->getKey());

            $actions->append('<a href="'.$route_salary.'" class="grid-row-view btn btn-xs btn-danger" data-toggle="tooltip" title="" data-original-title="Bảng doanh số tính lương">
            <i class="fa fa-heartbeat"></i>
                </a>');
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

        $form->saving(function (Form $form) {
            $form->salary = str_replace(",", "", $form->salary);
        });

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
        $report = Report::find($id);
        if ($report->status == 0) {
            
            $grid = new Grid(new ReportDetail());
            $grid->model()->where('sale_report_id', $id)
            ->orderBy(DB::raw("`success_order_payment` + `processing_order_payment`"), 'desc');

            $grid->header(function () use ($id) {
                $html = Report::find($id)->title;
                return "<h3 style='text-transform: uppercase;'>Phân tích hiệu quả trên / " . $html . "</h3>";
            });

            $grid->filter(function($filter) {
                $filter->expand();
                $filter->disableIdFilter();
                $filter->column(1/2, function ($filter) {
                    $filter->where(function ($query) {
                        $ids = SystemTeamSale::find($this->input)->members;
                        $query->whereIn('user_id', $ids);
                    }, 'Team Sale', 'team_sale')->select(SystemTeamSale::all()->pluck('name', 'id'));
                });
                
                $filter->column(1/2, function ($filter) {
                    $service = new UserService();
        
                    $filter->equal('user_id', 'Nhân viên kinh doanh')->select($service->GetListSaleEmployee());
                });
            
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
            $grid->order_number('Mã đơn hàng được tính')
            ->display(function () {
                if ($this->order_number == "") {
                    return 0;
                }
                $arr = explode(",", $this->order_number);
                return sizeof($arr);
            })
            ->expand(function ($model) {
                $arr = explode(",", $this->order_number);
    
                $info = [];
                foreach ($arr as $key => $value) {
                    $info[] = [
                        $key+1,
                        $value
                    ];
                }
            
                return new Table(['STT', 'Mã đơn'], $info);
            })->style('width: 100px; text-align: center;');
            $grid->success_order_service_fee('DOANH THU PHÍ DỊCH VỤ <br> (1)')->display(function () {
               $html = number_format($this->amount_percent_service);
               return "<span class='data-used'>".$html."</span>";
            })->style('text-align: right');
    
            $grid->total_transport_fee('DOANH THU VẬN TẢI <br> (2)')->display(function () {
                $total = $this->total_transport_fee;
                $person = ($total * 0.1);
    
                return "<span class='data-used'>".number_format($person)."</span>"
                 . " <br> <span style='color:red'>(".number_format($total).")</span> <br> <i> 10% tổng tiền doanh thu vận tải </i>";
            })->style('text-align: right');
    
            $grid->success_order_payment_rmb('DOANH THU TỶ GIÁ <br> (3)')->display(function () {
                $total = $this->amount_exchange_rate;
                $person = ($total * 30);
    
                return "<span class='data-used'>". number_format($person)."</span>" . " <br> <span style='color:red'>(".number_format($total).")</span> <br> <i> 30 * tổng tiền tệ đơn hàng </i>";
            })->style('text-align: right');
            $grid->offer_cn('Lợi nhuận đàm phán <br> (4)')->display(function () {
                $total = $this->amount_offer_vn;
                $person = $total * 0.85;
                return "<span class='data-used'>". number_format($person)."</span>" . " <br> <span style='color:red'>(".number_format($total).")</span> <br> <i> 85% tổng tiền đám phán đơn hàng </i>";
            })->style('text-align: right');
            $grid->success_order_new_customer("TỔNG DOANH THU <br> (5 = 1 + 2 + 3 + 4)")->display(function () {
                $service_fee = $this->amount_percent_service;
                $transport_payment = $this->total_transport_fee * 0.1;
                $exchange_rate_payment = ($this->amount_exchange_rate) * 30;
                $total = $this->amount_offer_vn;
                $person = $total * 0.85;
    
                $html = number_format(
                    $service_fee
                    + $transport_payment
                    + $exchange_rate_payment
                    + $person
                );
    
                return "<span class='data-used'>".$html."</span>";
            })->style('text-align: right; color: green;')->label('success');
    
            $grid->t9_pdv('PDV đơn cọc T9, thành công T10 <br> (6)')->display(function () {
                $html = number_format($this->t9_pdv);
               return "<span class='data-used'>".$html."</span>";
            });
    
            $grid->salary("TIỀN LƯƠNG THỰC NHẬN <br> (7)")->display(function () {
                if ($this->salary == null) {
                    $salary = 0;
                } else {
                    $salary = $this->salary;
                }
                return number_format($salary);
            })->editable()->style('text-align: right');
    
            $grid->success_order_payment_new_customer("HIỆU QUẢ SAU TRỪ LƯƠNG <br> (8 = 5 - 6 - 7)")->display(function () {
                if ($this->salary != 0) {
                    $service_fee = $this->amount_percent_service;
                    $transport_payment = $this->total_transport_fee * 0.1;
                    $exchange_rate_payment = ($this->amount_exchange_rate) * 30;
                    $total = $this->amount_offer_vn;
                    $person = $total * 0.85;
    
                    $total = (
                        $service_fee
                        + $transport_payment
                        + $exchange_rate_payment
                        + $person
                    );
    
                    if ($this->salary == null) {
                        $salary = 0;
                    } else {
                        $salary = $this->salary;
                    }
                    
                    $res = $total - $salary - $this->t9_pdv;
                    if ($res < 0) {
                        $label = 'danger';
                    } else {
                        $label = 'primary';
                    }
    
                    return "<span class='label label-".$label."'>"."<span class='data-used'>".number_format($res)."</span>"."</span>";
                }
    
                return "<span style='color:red'>Chưa điền tiền lương</span>";
            })->style('text-align: right');


            Admin::script($this->script());
            
        } else {
            $grid = new Grid(new SaleSalary());
            $grid->model()->whereReportId($id);

            $grid->header(function () use ($id) {
                $html = Report::find($id)->title;
                return "<h3 style='text-transform: uppercase;'>Phân tích hiệu quả trên / " . $html . "</h3>";
            });

            $grid->filter(function($filter) {
                $filter->expand();
                $filter->disableIdFilter();
                $filter->column(1/2, function ($filter) {
                    $filter->where(function ($query) {
                        $ids = SystemTeamSale::find($this->input)->members;
                        $query->whereIn('user_id', $ids);
                    }, 'Team Sale', 'team_sale')->select(SystemTeamSale::all()->pluck('name', 'id'));
                });
                
                $filter->column(1/2, function ($filter) {
                    $service = new UserService();
        
                    $filter->equal('user_id', 'Nhân viên kinh doanh')->select($service->GetListSaleEmployee());
                });
            
            });

            $grid->rows(function (Grid\Row $row) {
                $row->column('number', ($row->number+1));
            });
            $grid->column('number', 'STT');
            $grid->user_id('NHÂN VIÊN KINH DOANH')->display(function () {
                $html = $this->employee->name;
                $html .= "<br>";
                $html .= date('H:i d-m-Y', strtotime($this->employee->created_at));

                return $html;
            });$grid->order_number('Mã đơn hàng được tính')
            ->display(function () {
                return $this->po_success;
            })->style('width: 100px; text-align: center;');
            $grid->success_order_service_fee('DOANH THU PHÍ DỊCH VỤ (1)')->display(function () {
                $html = number_format($this->po_success_service_fee);
                return "<span class='data-used'>".$html."</span>";
            })->style('text-align: right');
            $grid->total_transport_fee('DOANH THU VẬN TẢI <br> (2)')->display(function () {
                $total = $this->trs_amount_all_customer;
                $person = ($total * 0.1);
    
                return "<span class='data-used'>".number_format($person)."</span>"
                 . " <br> <span style='color:red'>(".number_format($total).")</span> <br> <i> 10% tổng tiền doanh thu vận tải </i>";
            })->style('text-align: right');

            $grid->success_order_payment_rmb('DOANH THU TỶ GIÁ <br> (3)')->display(function () {
                $total = $this->po_success_total_rmb;
                $person = ($total * 30);
    
                return "<span class='data-used'>". number_format($person)."</span>" . " <br> <span style='color:red'>(".number_format($total).")</span> <br> <i> 30 * tổng tiền tệ đơn hàng </i>";
            })->style('text-align: right');

            $grid->offer_cn('Lợi nhuận đàm phán <br> (4)')->display(function () {
                $total = $this->po_success_offer;
                $person = $total * 0.85;
                return "<span class='data-used'>". number_format($person)."</span>" . " <br> <span style='color:red'>(".number_format($total).")</span> <br> <i> 85% tổng tiền đám phán đơn hàng </i>";
            })->style('text-align: right');

            $grid->success_order_new_customer("TỔNG DOANH THU <br> (5 = 1 + 2 + 3 + 4)")->display(function () {
                $service_fee = $this->po_success_service_fee;
                $transport_payment = $this->trs_amount_all_customer * 0.1;
                $exchange_rate_payment = ($this->po_success_total_rmb) * 30;
                $total = $this->po_success_offer;
                $person = $total * 0.85;
    
                $html = number_format(
                    $service_fee
                    + $transport_payment
                    + $exchange_rate_payment
                    + $person
                );
    
                return "<span class='data-used'>".$html."</span>";
            })->style('text-align: right; color: green;')->label('success');
            $grid->t9_pdv('PDV đơn cọc T9, thành công T10 <br> (6)')->display(function () {
                // $html = number_format($this->t9_pdv);
                $html = 0;
               return "<span class='data-used'>".$html."</span>";
            });
            $grid->salary("TIỀN LƯƠNG THỰC NHẬN <br> (7)")->display(function () {
                if ($this->employee_salary == null) {
                    $employee_salary = 0;
                } else {
                    $employee_salary = $this->employee_salary;
                }
                return number_format($employee_salary);
            })->editable()->style('text-align: right');
    
            $grid->success_order_payment_new_customer("HIỆU QUẢ SAU TRỪ LƯƠNG <br> (8 = 5 - 6 - 7)")->display(function () {
                if ($this->salary != 0) {
                    $service_fee = $this->po_success_service_fee;
                    $transport_payment = $this->trs_amount_all_customer * 0.1;
                    $exchange_rate_payment = ($this->po_success_total_rmb) * 30;
                    $total = $this->po_success_offer;
                    $person = $total * 0.85;
    
                    $total = (
                        $service_fee
                        + $transport_payment
                        + $exchange_rate_payment
                        + $person
                    );
    
                    if ($this->employee_salary == null) {
                        $employee_salary = 0;
                    } else {
                        $employee_salary = $this->employee_salary;
                    }
                    
                    $res = $total - $employee_salary - $this->t9_pdv;
                    if ($res < 0) {
                        $label = 'danger';
                    } else {
                        $label = 'primary';
                    }
    
                    return "<span class='label label-".$label."'>"."<span class='data-used'>".number_format($res)."</span>"."</span>";
                }
    
                return "<span style='color:red'>Chưa điền tiền lương</span>";
            })->style('text-align: right');


            Admin::script($this->script2());
        }
    
        $grid->paginate(50);
        $grid->disableBatchActions();
        $grid->disableCreateButton();
        $grid->disableActions();

        return $grid;
        
    }

    public function script2() {
        $route = route('admin.sale_salary_details.index');

        return <<<SCRIPT
            $('.column-salary a').each(function () {
                $(this).attr('data-url', "{$route}" + "/" + $(this).attr('data-pk'));
            });
            $( document ).ready(function() {
    
                $(document).on('click', '.editable-submit', function () {
                    // setTimeout(function () {
                    //     window.location.reload();
                    // }, 500);
                });

                // % row
                $('table').prepend(
                    '<tfoot style="text-align: right"><tr>'
                    + '<td colspan="2">Tiền đã tính % theo điều kiện</td>'
                    + '<td><span id="order">0</span></td>'
                    + '<td><span id="service-fee-total">0</span></td>'
                    + '<td><span id="transport-fee-total">0</span></td>'
                    + '<td><span id="exchange-fee-total">0</span></td>'
                    + '<td><span id="offer-fee-total">0</span></td>'
                    + '<td><span id="amount-fee-total">0</span></td>'
                    + '<td><span id="t9-pdv-fee-total">0</span></td>'
                    + '<td><span id="salary-fee-total">0</span></td>'
                    + '<td><span id="payment-fee-total">0</span></td>'
                    + '</tr></tfoot>'
                );

                getTotalHtml("column-success_order_service_fee", "service-fee-total", true);
                getTotalHtml("column-t9_pdv", "t9-pdv-fee-total", true);
                getTotalHtml("column-total_transport_fee", "transport-fee-total", true);
                getTotalHtml("column-success_order_payment_rmb", "exchange-fee-total", true);
                getTotalHtml("column-success_order_new_customer", "amount-fee-total", true);
                getTotalHtml("column-salary", "salary-fee-total", false);
                getTotalHtml("column-offer_cn", "offer-fee-total", true);

                // getTotalHtml("column-success_order_payment_new_customer", "payment-fee-total", true);

                function getTotalHtml(column_class, element_append_id, editable = true) {
                    let ele = null;
                    if (editable == true) {
                        console.log('oke');
                        ele = $('tbody .' + column_class + ' .data-used');
                    } else {
                        ele = $('tbody .' + column_class + ' a.editable');
                    }
                    let total = 0;

                    if (ele != null) {
                        ele.each( function( i, el ) {
                            var elem = $( el );
                            let html = $.trim(elem.html());

                            html = html.replace(/\,/g, '');
                            html = parseInt(html);
        
                            total += html;
                        });

                        if (element_append_id != "") {
                            console.log(total);
                            $("#"+ element_append_id).html(number_format(total));
                        } 
                    }

                    return total;
                }

                let final_total = $('#amount-fee-total').html();
                final_total = final_total.replace(/\,/g, '');
                final_total = parseInt(final_total);

                let final_pdv_t9 = $("#t9-pdv-fee-total").html();
                final_pdv_t9 = final_pdv_t9.replace(/\,/g, '');
                final_pdv_t9 = parseInt(final_pdv_t9);

                let final_salary = $('#salary-fee-total').html();
                final_salary = final_salary.replace(/\,/g, '');
                final_salary = parseInt(final_salary);

                let owed = final_total - final_pdv_t9 - final_salary;

                $("#payment-fee-total").html(number_format(owed));
                console.log(final_total, "final_total");

                // total row
                $('table').prepend(
                    '<tfoot style="text-align: right; background: orange !important;"><tr>'
                    + '<td colspan="2">Tổng tiền các mục</td>'
                    + '<td><span id="order">0</span></td>'
                    + '<td><span id="portal_service-fee-total">0</span></td>'
                    + '<td><span id="portal_transport-fee-total">0</span></td>'
                    + '<td><span id="portal_exchange-fee-total">0</span></td>'
                    + '<td><span id="portal_offer-fee-total">0</span></td>'
                    + '<td><span id="portal_amount-fee-total">0</span></td>'
                    + '<td><span id="portal_t9-pdv-fee-total">0</span></td>'
                    + '<td><span id="portal_salary-fee-total">0</span></td>'
                    + '<td><span id="portal_payment-fee-total">0</span></td>'
                    + '</tr></tfoot>'
                );

                getTotalHtml("column-success_order_service_fee", "service-fee-total", true);
                getTotalHtml("column-success_order_service_fee", "portal_service-fee-total", true);

                let per_transport_fee_total = parseInt($('#transport-fee-total').html().replace(/\,/g, ''));
                $('#portal_transport-fee-total').html(number_format(per_transport_fee_total / 10 * 100));

                let per_exchange_fee_total = parseInt($('#exchange-fee-total').html().replace(/\,/g, ''));
                $('#portal_exchange-fee-total').html(number_format(per_exchange_fee_total / 30) + " (Tệ)");


                let per_offer_fee_total = parseInt($('#offer-fee-total').html().replace(/\,/g, ''));
                $('#portal_offer-fee-total').html(number_format(per_offer_fee_total / 85 * 100));

                function number_format(number, decimals, dec_point, thousands_sep) {
                    // Strip all characters but numerical ones.
                    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
                    var n = !isFinite(+number) ? 0 : +number,
                        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
                        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
                        s = '',
                        toFixedFix = function (n, prec) {
                            var k = Math.pow(10, prec);
                            return '' + Math.round(n * k) / k;
                        };
                    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
                    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
                    if (s[0].length > 3) {
                        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
                    }
                    if ((s[1] || '').length < prec) {
                        s[1] = s[1] || '';
                        s[1] += new Array(prec - s[1].length + 1).join('0');
                    }
                    return s.join(dec);
                }
            });
SCRIPT;
    }

    public function script() {
        $route = route('admin.revenue_reports.index');

        return <<<SCRIPT
            $('.column-salary a').each(function () {
                $(this).attr('data-url', "{$route}" + "/" + $(this).attr('data-pk'));
            });
            $( document ).ready(function() {
    
                $(document).on('click', '.editable-submit', function () {
                    setTimeout(function () {
                        window.location.reload();
                    }, 500);
                });

                // % row
                $('table').prepend(
                    '<tfoot style="text-align: right"><tr>'
                    + '<td colspan="2">Tiền đã tính % theo điều kiện</td>'
                    + '<td><span id="order">0</span></td>'
                    + '<td><span id="service-fee-total">0</span></td>'
                    + '<td><span id="transport-fee-total">0</span></td>'
                    + '<td><span id="exchange-fee-total">0</span></td>'
                    + '<td><span id="offer-fee-total">0</span></td>'
                    + '<td><span id="amount-fee-total">0</span></td>'
                    + '<td><span id="t9-pdv-fee-total">0</span></td>'
                    + '<td><span id="salary-fee-total">0</span></td>'
                    + '<td><span id="payment-fee-total">0</span></td>'
                    + '</tr></tfoot>'
                );

                getTotalHtml("column-success_order_service_fee", "service-fee-total", true);
                getTotalHtml("column-t9_pdv", "t9-pdv-fee-total", true);
                getTotalHtml("column-total_transport_fee", "transport-fee-total", true);
                getTotalHtml("column-success_order_payment_rmb", "exchange-fee-total", true);
                getTotalHtml("column-success_order_new_customer", "amount-fee-total", true);
                getTotalHtml("column-salary", "salary-fee-total", false);
                getTotalHtml("column-offer_cn", "offer-fee-total", true);

                // getTotalHtml("column-success_order_payment_new_customer", "payment-fee-total", true);

                function getTotalHtml(column_class, element_append_id, editable = true) {
                    let ele = null;
                    if (editable == true) {
                        console.log('oke');
                        ele = $('tbody .' + column_class + ' .data-used');
                    } else {
                        ele = $('tbody .' + column_class + ' a.editable');
                    }
                    let total = 0;

                    if (ele != null) {
                        ele.each( function( i, el ) {
                            var elem = $( el );
                            let html = $.trim(elem.html());

                            html = html.replace(/\,/g, '');
                            html = parseInt(html);
        
                            total += html;
                        });

                        if (element_append_id != "") {
                            console.log(total);
                            $("#"+ element_append_id).html(number_format(total));
                        } 
                    }

                    return total;
                }

                let final_total = $('#amount-fee-total').html();
                final_total = final_total.replace(/\,/g, '');
                final_total = parseInt(final_total);

                let final_pdv_t9 = $("#t9-pdv-fee-total").html();
                final_pdv_t9 = final_pdv_t9.replace(/\,/g, '');
                final_pdv_t9 = parseInt(final_pdv_t9);

                let final_salary = $('#salary-fee-total').html();
                final_salary = final_salary.replace(/\,/g, '');
                final_salary = parseInt(final_salary);

                let owed = final_total - final_pdv_t9 - final_salary;

                $("#payment-fee-total").html(number_format(owed));
                console.log(final_total, "final_total");

                // total row
                $('table').prepend(
                    '<tfoot style="text-align: right; background: orange !important;"><tr>'
                    + '<td colspan="2">Tổng tiền các mục</td>'
                    + '<td><span id="order">0</span></td>'
                    + '<td><span id="portal_service-fee-total">0</span></td>'
                    + '<td><span id="portal_transport-fee-total">0</span></td>'
                    + '<td><span id="portal_exchange-fee-total">0</span></td>'
                    + '<td><span id="portal_offer-fee-total">0</span></td>'
                    + '<td><span id="portal_amount-fee-total">0</span></td>'
                    + '<td><span id="portal_t9-pdv-fee-total">0</span></td>'
                    + '<td><span id="portal_salary-fee-total">0</span></td>'
                    + '<td><span id="portal_payment-fee-total">0</span></td>'
                    + '</tr></tfoot>'
                );

                getTotalHtml("column-success_order_service_fee", "service-fee-total", true);
                getTotalHtml("column-success_order_service_fee", "portal_service-fee-total", true);

                let per_transport_fee_total = parseInt($('#transport-fee-total').html().replace(/\,/g, ''));
                $('#portal_transport-fee-total').html(number_format(per_transport_fee_total / 10 * 100));

                let per_exchange_fee_total = parseInt($('#exchange-fee-total').html().replace(/\,/g, ''));
                $('#portal_exchange-fee-total').html(number_format(per_exchange_fee_total / 30) + " (Tệ)");


                let per_offer_fee_total = parseInt($('#offer-fee-total').html().replace(/\,/g, ''));
                $('#portal_offer-fee-total').html(number_format(per_offer_fee_total / 85 * 100));

                function number_format(number, decimals, dec_point, thousands_sep) {
                    // Strip all characters but numerical ones.
                    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
                    var n = !isFinite(+number) ? 0 : +number,
                        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
                        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
                        s = '',
                        toFixedFix = function (n, prec) {
                            var k = Math.pow(10, prec);
                            return '' + Math.round(n * k) / k;
                        };
                    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
                    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
                    if (s[0].length > 3) {
                        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
                    }
                    if ((s[1] || '').length < prec) {
                        s[1] = s[1] || '';
                        s[1] += new Array(prec - s[1].length + 1).join('0');
                    }
                    return s.join(dec);
                }
            });
SCRIPT;
    }

    public function salary($id, Content $content) {
        return $content
            ->title($this->title())
            ->description("Bảng tổng hợp doanh số, hiệu quả làm việc")
            ->body($this->salaryGrid($id));
    }

    public function salaryGrid($id) {
        $grid = new Grid(new SaleSalary());
        $grid->model()->whereId(-1);

        $grid->filter(function($filter) {
            $filter->expand();
            $filter->disableIdFilter();

            $service = new UserService();
            $portal_sales = $service->GetListSaleEmployee();

            $filter->equal('user_id', 'Nhân viên kinh doanh')->select($portal_sales);
            $filter->equal('team_sale_id', 'Team Sale')->select(SystemTeamSale::all()->pluck('name', 'id'));
        });

        $grid->header(function () use ($id) {

            if (Admin::user()->isRole('sale_employee')) {
               
                if (Admin::user()->isRole('sale_manager')) {
                    $data = SaleSalary::whereReportId($id);
                } else {
                
                    $flag = SystemTeamSale::where('leader', Admin::user()->id)->first();
                    
                    if ($flag != "" && $flag->count() > 0) {
                        $members = $flag->members;
                        $data = SaleSalary::whereReportId($id)->whereIn('user_id', $members);
                    } else {

                        $user_id = Admin::user()->id;
                        $data = SaleSalary::whereReportId($id)->whereUserId($user_id);
                    }
                }
            } else if (Admin::user()->isRole('ar_employee') || Admin::user()->isRole('administrator')) {
                $data = SaleSalary::whereReportId($id);
            }


            $report = Report::find($id);
            if (isset($_GET['user_id']) && $_GET['user_id'] != "") {
                $data->where('user_id', $_GET['user_id']);
            }

            if (isset($_GET['team_sale_id']) && $_GET['team_sale_id'] != "") {
                $team = SystemTeamSale::find($_GET['team_sale_id']);

                if ($team) {
                    $members = $team->members;
                    $data->whereIn('user_id', $members);
                }
            }

            $data = $data->get();
            
            return view('admin.system.report_portal.sale_salary', compact('data', 'report'));
        });

        $grid->disableActions();
        $grid->disableBatchActions();
        $grid->disableColumnSelector();
        $grid->disableCreateButton();
        $grid->disablePagination();
        $grid->disablePerPageSelector();
        $grid->disableExport();
        
        return $grid;
    }

    public function revenueReportApi() {

        return response()->json([
            'status'    => 200,
            'data'  => [] // Report::orderBy('order', 'desc')->get()
        ]);
    }

    public function fetchRevenueReportApi($id) {
        $salary_users = SaleSalary::whereReportId($id)->pluck('user_id')->toArray();
        $sales_users = ReportDetail::whereSaleReportId($id)->pluck('user_id')->toArray();

        $user_ids = array_merge($salary_users, $sales_users);
        
        $users = User::select('id', 'name', 'created_at')->whereIn('id', $user_ids)->get();

        return response()->json([
            'status'    => 200,
            'data'  =>  $users
        ]);
    }

    public function saleRevenueReportApi($report_id, $user_id) {
        $row = ReportDetail::where('sale_report_id', $report_id)->whereUserId($user_id)->first();
        $data = [
            [
                'title' =>  'Khách hàng',
                'data'    =>  [
                    'Cũ'    =>  $row->total_customer - $row->new_customer,
                    'Mới'   =>  $row->new_customer,
                    'Tổng'  =>  $row->total_customer,
                    'Tổng âm ví'    =>  number_format($row->total_customer_wallet)
                ]
            ],
            [
                'title' =>  'Đơn hàng hoàn thành',
                'data' =>   [
                    'Số lượng'  =>  $row->success_order,
                    'Doanh số khách hàng mới' => number_format($row->success_order_payment_new_customer),
                    'Tổng doanh số' =>  number_format($row->success_order_payment),
                    'Phí dịch vụ'   =>  number_format($row->success_order_service_fee)
                ]
            ],
            [
                'title' =>  'Đơn chưa hoàn thành',
                'data'  =>  [
                    'Số lượng'  =>  $row->processing_order,
                    'Doanh số khách hàng mới' =>    number_format($row->processing_order_payment_new_customer),
                    'Tổng doanh số' =>  number_format($row->processing_order_payment),
                    'Công nợ trên đơn'  => number_format($row->owed_processing_order_payment),
                    'Phí dịch vụ'   =>  number_format($row->processing_order_service_fee)
                ]   
            ],
            [
                'title' =>  'Phí dịch vụ',
                'data'  =>  [
                    'Tổng'  =>  number_format($row->processing_order_service_fee + $row->success_order_service_fee)
                ]
            ],
            [
                'title' =>  'Vận chuyển',
                'data'  => [
                    'Tổng KG'   =>  number_format($row->total_transport_weight, 2, '.', ''),
                    'Tổng M3'   =>  number_format($row->total_transport_m3, 3, '.', ''),
                    'Tổng KG Khách hàng mới'   =>  number_format($row->total_transport_weight_new_customer, 2, '.', ''),
                    'Doanh thu Khách hàng mới'   =>  number_format($row->total_transport_fee_new_customer),
                    'Tổng doanh thu'   =>  number_format($row->total_transport_fee)
                ]    
            ],
            [
                'title' =>  'Tổng doanh số tháng',
                'data'  =>  [
                    'Tổng'  =>  number_format($row->success_order_payment + $row->processing_order_payment)
                ]
            ]
        ];
        return response()->json([
            'status'    => 200,
            'data'  =>  $data
        ]);
    }

    public function salaryReportApi($report_id, $user_id) {
        $value = SaleSalary::whereReportId($report_id)->whereUserId($user_id)->first();

        $data = [];
        if ($value) {
            $data = [
                [
                    'title' =>  'Khách hàng',
                    'data'  =>  [
                        'KH mới'    =>  $value->new_customer,
                        'KH cũ'    =>  $value->old_customer,
                        'Tổng số'    =>  $value->all_customer,
                        'Công nợ KH mới'    =>  number_format($value->owed_wallet_new_customer),
                        'Công nợ KH cũ'    =>  number_format($value->owed_wallet_old_customer),
                        'Công nợ tổng'    =>  number_format($value->owed_wallet_all_customer),
                    ]
                ],
                [
                    'title' =>  'Đơn hàng Order hoàn thành',
                    'data' =>   [
                        'Số lượng'  =>  $value->po_success,
                        'Doanh số KH mới'  =>  number_format($value->po_success_new_customer),
                        'Doanh số KH cũ'  =>  number_format($value->po_success_old_customer),
                        'Tổng doanh số'  =>   number_format($value->po_success_all_customer),
                        'Phí dịch vụ'  =>  number_format($value->po_success_service_fee),
                        'Tổng tệ'  =>  number_format($value->po_success_total_rmb),
                        'Đàm phán'  =>  number_format($value->po_success_offer),
                    ]
                ],
                [
                    'title' =>  'Đơn hàng Order chưa hoàn thành',
                    'data'  =>  [
                        'Số lượng'  =>  $value->po_not_success,
                        'Doanh số KH mới'  =>  number_format($value->po_not_success_new_customer),
                        'Doanh số KH cũ'  =>  number_format($value->po_not_success_old_customer),
                        'Tổng doanh số'  =>  number_format($value->po_not_success_all_customer),
                        'Phí dịch vụ'  =>  number_format($value->po_not_success_service_fee),
                        'Tổng cọc'  =>  number_format($value->po_not_success_deposited),
                        'Công nợ'  =>  number_format($value->po_not_success_owed)
                    ]
                ],
                [
                    'title' =>  'Đơn hàng vận chuyển',
                    'data' =>   [
                        'Số lượng'  =>  $value->transport_order ,
                        'KG KH mới'  =>  $value->trs_kg_new_customer,
                        'KG KH cũ'  =>  $value->trs_kg_old_customer,
                        'Tổng KG'  =>  $value->trs_kg_all_customer,
                        'M3 KH mới'  =>  $value->trs_m3_new_customer,
                        'M3 KH cũ'  =>  $value->trs_m3_old_customer,
                        'Tổng M3'  =>  $value->trs_m3_all_customer,
                        'Doanh thu KH mới'  =>  number_format($value->trs_amount_new_customer) ,
                        'Doanh thu KH cũ'  =>  number_format($value->trs_amount_old_customer),
                        'Tổng doanh thu'  =>  number_format($value->trs_amount_all_customer),
                    ]
                ],
                [
                    'title' =>  'Tổng số cuối',
                    'data'  =>  [
                        'Số đơn Order hoàn thành' =>   $value->po_success,
                        'Phí dịch vụ (100%)' =>   number_format($value->po_success_service_fee),
                        'Doanh thu vận chuyển (10%)' =>   number_format($value->trs_amount_all_customer*0.1),
                        'Doanh thu tỷ giá (30 * Tổng giá tệ)' =>   number_format($value->po_success_total_rmb*30),
                        'Doanh thu đàm phán' =>   number_format($value->po_success_offer*0.85),
                        'Tổng doanh thu' =>  number_format(
                            $value->po_success_service_fee + ($value->trs_amount_all_customer*0.1) + ($value->po_success_total_rmb*30) + ($value->po_success_offer*0.85)
                        ) ,
                    ]
                ]
            ];
        }

        return response()->json([
            'status'    => 200,
            'data'  =>  $data
        ]);
    }
}
