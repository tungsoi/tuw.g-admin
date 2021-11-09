<?php

namespace App\Admin\Controllers\PurchaseOrder;

use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Models\PurchaseOrder\Complaint;
use App\Models\PurchaseOrder\ComplaintComment;
use App\Models\PurchaseOrder\ComplaintNotification;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\User;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Layout\Column;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Admin\Services\OrderService;
use App\Admin\Services\UserService;

class ComplaintController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title;

    public function __construct()
    {
        $this->title = 'Khiếu nại đơn hàng';
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Complaint);
        $grid->model()->orderBy('created_at', 'desc')
        ->with('order');

        $service = new UserService();
        $saleEmployee = $service->GetListSaleEmployee();
        $orderEmployee = $service->GetListOrderEmployee();

        $grid->filter(function($filter) use ($saleEmployee, $orderEmployee) {
            $filter->expand();
            $filter->disableIdFilter();

            $order_ids = [];
            if (Admin::user()->isRole('sale_employee'))
            {
                $order_ids = PurchaseOrder::whereIn('status', [9, 5, 7])
                ->where('supporter_sale_id', Admin::user()->id)->orderBy('id', 'desc')->get()->pluck('order_number', 'id');
            }
            else if (Admin::user()->isRole('order_employee')){
                $order_ids = PurchaseOrder::whereIn('status', [9, 5, 7])
                ->where('supporter_order_id', Admin::user()->id)->orderBy('id', 'desc')->get()->pluck('order_number', 'id');
            }
            else {
                $order_ids = PurchaseOrder::whereIn('status', [9, 5, 7])
                            ->orderBy('id', 'desc')->get()->pluck('order_number', 'id');
            }
            $filter->column(1/4, function ($filter) use ($order_ids) {
                $filter->equal('order_id', 'Mã đơn hàng')->select($order_ids);
                $filter->equal('status', 'Trạng thái')->select(Complaint::STATUS);
            });

            $service = new UserService();
            $filter->column(1/4, function ($filter) use ($saleEmployee) {
                
                $filter->where(function ($query){
                    $order_ids = PurchaseOrder::whereIn('status', [9, 5, 7])
                                ->where('supporter_sale_id', $this->input)
                                ->get()->pluck('id');

                    $query->whereIn('order_id', $order_ids);
                }, 'Nhân viên kinh doanh', 'supporter_sale_id')->select($saleEmployee);
            });

            $filter->column(1/4, function ($filter) use ($orderEmployee) {
                $filter->where(function ($query) {
                    $order_id = $this->input;
                    $order_ids = PurchaseOrder::whereIn('status', [9, 5, 7])
                                ->where('supporter_order_id', $order_id)
                                ->get()->pluck('id');

                    $query->whereIn('order_id', $order_ids);
                }, 'Nhân viên đặt hàng', 'supporter_order_id')->select($orderEmployee);
            });
            $filter->column(1/4, function ($filter) {
                $filter->between('created_at', "Ngày tạo khiếu nại")->date();
            });
            
            Admin::style('
                #filter-box label {
                    padding: 0px !important;
                    padding-top: 10px;
                    font-weight: 600;
                    font-size: 12px;
                }
                #filter-box .col-sm-2 {
                    width: 100% !important;
                    text-align: left;
                    padding: 0px 15px 3px 15px !important;
                }
                #filter-box .col-sm-8 {
                    width: 100% !important;
                }
            ');
        });

        if (Admin::user()->isRole('sale_employee')) 
        {
            if (Admin::user()->isRole('sale_manager')) {
                // all
            } else {
                $customers = User::select('id')->whereIsCustomer(1)->where('staff_sale_id', Admin::user()->id)->get()->pluck('id')->toArray();
                $order_ids = PurchaseOrder::select('id')->whereIn('customer_id', $customers)->get()->pluck('id')->toArray();
                $grid->model()->whereIn('order_id', $order_ids);
            }
        }
        else if (Admin::user()->isRole('order_employee') && ! Admin::user()->isRole('order_manager')) 
        {
            $orders = PurchaseOrder::where('supporter_order_id', Admin::user()->id)->get()->pluck('id');
            $grid->model()->whereIn('order_id', $orders);
        } else {
            // show all
        }

        $grid->header(function ($header) {
            $sale_id = isset($_GET['supporter_order_id']) ? $_GET['supporter_order_id'] : null;
            $time = isset($_GET['created_at']) ? $_GET['created_at'] : [];

            if (sizeof($time) > 0 && $time['start'] != null && $time['end'] != null) {
                $timeline = [
                    $time['start'],
                    $time['end']
                ];
            } else {
                $timeline = [
                    "2020-10-01",
                    "2050-12-30"
                ];
            }

            $order_ids = null;
            if ($sale_id != null) {
                $order_ids = PurchaseOrder::where('supporter_order_id', $sale_id)->pluck('id');
            }

            if ($order_ids != null && sizeof($order_ids) > 0) {
                $complaint = Complaint::whereIn('order_id', $order_ids)
                    ->whereBetween('created_at', $timeline)
                    ->count();
                $complaint_success = Complaint::whereIn('order_id', $order_ids)
                    ->whereBetween('created_at', $timeline)
                    ->whereStatus(Complaint::DONE)
                    ->count();
            } else {
                $complaint = Complaint::whereBetween('created_at', $timeline)->count();
                $complaint_success = Complaint::whereStatus(Complaint::DONE)
                    ->whereBetween('created_at', $timeline)
                    ->count();
            }

            if ($complaint == 0) {
                $percent = 0;
            } else {

                $percent = number_format($complaint_success/$complaint*100, 2);
            }

            return view('admin.complaint_progress', compact('percent', 'complaint_success', 'complaint'));
        });

        $grid->rows(function (Grid\Row $row) {
            $row->column('number', ($row->number+1));
        });
        $grid->column('number', 'STT');
        $grid->order_id('Mã đơn hàng')->display(function () {
            // return $this->order->order_number ?? "";
            $order = PurchaseOrder::find($this->order_id);
            $data = [
                'order'   =>  [
                    'is_label'   =>  true,
                    'color'     =>  'primary',
                    'text'      =>  $this->order->order_number ?? ""
                ],
                'customer'  =>  [
                    'is_label'  =>  true,
                    'color' =>  'default',
                    'text'  =>  $order->customer->symbol_name ?? ""
                ]
            ];            
            return view('admin.system.core.list', compact('data'));
        });

        $grid->column('sale_staff', 'Nhân viên')->display(function () use ($saleEmployee, $orderEmployee) {
            $data = [
                'order'   =>  [
                    'is_label'   =>  false,
                    'text'      =>  "- Sale: " 
                    . ( ($this->order->customer->staff_sale_id != "" && isset($saleEmployee[$this->order->customer->staff_sale_id]))
                    ? $saleEmployee[$this->order->customer->staff_sale_id]
                    : "")
                ],
                'customer'  =>  [
                    'is_label'  =>  false,
                    'text'  => "- Order: "
                    . ( ($this->order->supporter_order_id != "" && isset($orderEmployee[$this->order->supporter_order_id]))
                    ? $orderEmployee[$this->order->supporter_order_id]
                    : "")
                ],
                'time'  =>  [
                    'is_label'  =>  false,
                    'text'  =>  "- Ngày tạo: ". date('H:i | d-m-Y', strtotime($this->created_at))
                ]
            ];            
            return view('admin.system.core.list', compact('data'));
        })->width(230);

        $grid->image('Ảnh sản phẩm')->lightbox(['width' => 100, 'height' => 100])->width(300);
        $grid->item_name('Tên sản phẩm')->width(250)->display(function () {
            $data = [
                'order'   =>  [
                    'is_label'   =>  false,
                    'text'      =>  "- Tên SP: " . "<b>".$this->item_name."</b>"
                ],
                'transport_code'  =>  [
                    'is_label'  =>  false,
                    'text'  => "- MVD: ". $this->transport_code
                ],
                'payment_code'  =>  [
                    'is_label'  =>  false,
                    'text'  =>  "- MGD: ". $this->payment_code
                ]
            ];            
            return view('admin.system.core.list', compact('data'));
        });
        $grid->item_price('Giá');
        $grid->content('Nội dung Khiếu nại');
        // $grid->comment('Số trao đổi')->display(function () {
        //     return ComplaintComment::where('complaint_id', $this->id)->count();
        // });
        $grid->status('Trạng thái')->display(function () {
            $html = Complaint::STATUS[$this->status];
            $time = "";

            switch ($this->status)
            {
                case Complaint::NEW:
                    $time = " (".$this->created_at.")";
                    break;
                case Complaint::PROCESS_NORMAL:
                    $time = " (".$this->begin_handled_at.")";
                    break;
                case Complaint::ADMIN_CONFIRM_SUCCESS:
                    $time = " (".$this->admin_finished_at.")";
                    break;
                case Complaint::DONE:
                    $time = " (".$this->succesed_at.")";
                    break;
                default: 
                    $time = null;
                    break;
            }

            $data = [
                'amount_rmb'   =>  [
                    'is_label'   =>  true,
                    'color'     =>  Complaint::LABEL[$this->status],
                    'text'      =>  Complaint::STATUS[$this->status]
                ],
                'time'  =>  [
                    'is_label'  =>  true,
                    'color'     =>  'default',
                    'text'  =>  $time
                ]
            ];            
            return view('admin.system.core.list', compact('data'));
        });

        $grid->disableColumnSelector();
        $grid->disableBatchActions();
        $grid->disableExport();
        $grid->paginate(10);

        return $grid;
    }

    /**
     * Show interface.
     *
     * @param mixed   $id
     * @param Content $content
     *
     * @return Content
     */
    public function showComplaint($id, Content $content)
    {
        if (isset($_GET['status']) && $_GET['status'] == "viewed")
        {
            ComplaintNotification::whereComplaintId($id)
            ->whereUserId(Admin::user()->id)
            ->whereStatus(1)
            ->update([
                'status' => 0
            ]);
        }

        return $content
        ->title($this->title())
        ->description($this->description['show'] ?? trans('admin.show'))
        ->row(function (Row $row) use ($id)
        {
            if (Admin::user()->isRole('order_employee') && in_array(Complaint::find($id)->status, [Complaint::PROCESS_NORMAL, Complaint::PROCESS_AGENT])) {
                $row->column(12, function (Column $column) use ($id) 
                {
                    $column->append((new Box('', $this->AdminConfirmSuccess($id))));
                });
            }

            if (Admin::user()->isRole('sale_employee') && Complaint::find($id)->status == Complaint::ADMIN_CONFIRM_SUCCESS) {
                $row->column(12, function (Column $column) use ($id) 
                {
                    $column->append((new Box('', $this->saleConfirmSuccess($id))));
                });
            }

            $row->column(12, function (Column $column) use ($id) 
            {
                $column->append((new Box('', $this->detail($id))));
            });

            if (! Admin::user()->isRole('customer'))
            {
                $row->column(6, function (Column $column) use ($id) 
                {
                    $column->append((new Box("", $this->listCommentOrderSale($id)->render())));
                });
            }
            
            if (! Admin::user()->isRole('customer'))
            {
                $row->column(6, function (Column $column) use ($id)
                {
                    $column->append((new Box('', $this->formSubComment($id, 2))));
                });
            }
            
            
        });
    }

    public function AdminConfirmSuccess($id) {
        return view('admin.admin-confirm-success-complaint', compact('id'))->render();
    }

    public function saleConfirmSuccess($id) {
        return view('admin.customer-confirm-success-complaint', compact('id'))->render();
    }

    public function storeAdminConfirmSuccess(Request $request) {
        // order xac nhan da xu ly => gui thong bao cho sale
        Complaint::find($request->id)->update([
            'status'    =>  Complaint::ADMIN_CONFIRM_SUCCESS,
            'admin_finished_at' => now()
        ]);

        $complaint = Complaint::find($request->id);
        $order = PurchaseOrder::find($complaint->order_id);
        ComplaintNotification::firstOrCreate([
            'order_id'  =>  $complaint->order_id,
            'complaint_id'  =>  $request->id,
            'user_id'   =>  $order->supporter_sale_id,
            'content'   => Admin::user()->name .' xác nhận đã xử lý khiếu nại đơn hàng '.$order->order_number,
            'status'    =>  1
        ]);

        admin_toastr('Lưu thành công', 'success');
        return redirect()->back();
    }

    public function storeCustomerConfirmSuccess(Request $request) {
        Complaint::find($request->id)->update([
            'status'    =>  Complaint::DONE,
            'succesed_at'   =>  now()
        ]);

        $complaint = Complaint::find($request->id);
        $order = PurchaseOrder::find($complaint->order_id);
        ComplaintNotification::firstOrCreate([
            'order_id'  =>  $complaint->order_id,
            'complaint_id'  =>  $request->id,
            'user_id'   =>  $order->supporter_order_id,
            'content'   => Admin::user()->name .' xác nhận đã xử lý thành công khiếu nại đơn hàng '.$order->order_number,
            'status'    =>  1
        ]);

        admin_toastr('Lưu thành công', 'success');
        return redirect()->back();
    }

    public function listComment($id) {
        $grid = new Grid(new ComplaintComment());
        $grid->model()->where('complaint_id', $id)->whereType(1);

        $grid->header(function () {
            return '<h4 style="text-align: center"> Khách hàng &nbsp;<i class="fa fa-exchange" aria-hidden="true"></i> &nbsp; Nhân viên kinh doanh </h4>';
        });
        $grid->column('content', 'Bình luận')->display(function () {
            $html = "<b>".User::find($this->user_created_id)->name." (".date('H:i | d-m-Y', strtotime($this->created_at)).") </b>: ";
            $html .= $this->content;
            return $html;
        });
        // $grid->user_created_id('Người tạo')->display(function () {
        //     return User::find($this->user_created_id)->name ?? "";
        // })->width(200);

        // $grid->content('Nội dung')->width(800);

        // $grid->created_at(trans('admin.created_at'))->display(function () {
        //     return date('H:i | d-m-Y', strtotime($this->created_at));
        // });

        $grid->disableActions();
        $grid->disableColumnSelector();
        $grid->disableCreateButton();
        $grid->disableBatchActions();
        $grid->disableFilter();
        $grid->disableExport();
        $grid->disablePagination();
        $grid->disableDefineEmptyPage();

        Admin::style('
            .grid-box {
                margin: 0px !important;
                border: none !important;
            }   
            .box-footer {
                padding: 0px !important;
            }

        ');

        return $grid;
    }

    public function listCommentOrderSale($id) {
        $grid = new Grid(new ComplaintComment());
        $grid->model()->where('complaint_id', $id)->where('type', 2);

        $grid->header(function () {
            return '<h4 style="text-align: center"> Nhân viên kinh doanh &nbsp;<i class="fa fa-exchange" aria-hidden="true"></i> &nbsp; Nhân viên đặt hàng </h4>';
        });
        $grid->column('content', 'Bình luận')->display(function () {
            $html = "<b>".User::find($this->user_created_id)->name." (".date('H:i | d-m-Y', strtotime($this->created_at)).") </b>: ";
            $html .= "<br><br><div class='content-comment'>".$this->content."</div>";
            return $html;
        });
        // $grid->user_created_id('Người tạo')->display(function () {
        //     return User::find($this->user_created_id)->name ?? "";
        // })->width(200);

        // $grid->content('Nội dung')->width(800);

        // $grid->created_at(trans('admin.created_at'))->display(function () {
        //     return date('H:i | d-m-Y', strtotime($this->created_at));
        // });

        $grid->disableActions();
        $grid->disableColumnSelector();
        $grid->disableCreateButton();
        $grid->disableBatchActions();
        $grid->disableFilter();
        $grid->disableExport();
        $grid->disablePagination();
        $grid->disableDefineEmptyPage();

        Admin::style('
            .grid-box {
                margin: 0px !important;
                border: none !important;
            }   
            .box-footer {
                padding: 0px !important;
            }
            form .col-sm-2, form .col-sm-8 {
                width: 100%;
                text-align: left !important;
                padding: 0px !important;
            }
            .box {
                border: none !important;
            }
            .content-comment img {
                width: 200px !important;
            }

        ');

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
        $show = new Show(Complaint::findOrFail($id));
        $show->panel()
            ->title('Thông tin khiếu nại');
        $show->status('Trạng thái')->as(function ($content) {
            return Complaint::STATUS[$this->status];
        });
        $show->customer_id('Mã khách hàng')->as(function () {
            $order = PurchaseOrder::find($this->order_id);
            return $order->customer->symbol_name ?? "";
        });
        $show->order_id('Mã đơn hàng')->as(function () {
            return $this->order->order_number ?? "";
        });
        $show->image('Ảnh sản phẩm')->image("", 100, 100);
        $show->item_name('Tên sản phẩm');
        $show->item_price('Giá sản phẩm');
        $show->content('Nội dung Khiếu nại');
        $show->panel()
        ->tools(function ($tools) {
            $tools->disableEdit();
            $tools->disableList();
            $tools->disableDelete();
        });;


        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Complaint);
        
        $userId = Admin::user()->id;
        $customers = User::where('staff_sale_id', $userId)->get()->pluck('id');

        $form->select('order_id', 'Mã đơn hàng')
        ->options(
            PurchaseOrder::select('order_number', 'id')->where('status', 9)
            ->orderBy('id', 'desc')
            ->pluck('order_number', 'id')
        )->rules('required');
        $form->multipleImage('image', 'Ảnh sản phẩm');
        $form->text('item_name', 'Tên sản phẩm')->rules('required');
        $form->text('item_price', 'Giá sản phẩm')->rules('required');
        $form->textarea('content', 'Nội dung Khiếu nại')->rules('required');
        $form->text('transport_code', 'Mã vận đơn')->rules('required');
        $form->text('payment_code', 'Mã giao dịch');

        $form->disableEditingCheck();
        $form->disableCreatingCheck();
        $form->disableViewCheck();

        $form->saved(function (Form $form) {
            if ($form->model()->id) {
                ComplaintNotification::firstOrCreate([
                    'order_id'  =>  $form->model()->order_id,
                    'complaint_id'  =>  $form->model()->id,
                    'user_id'   =>  PurchaseOrder::find($form->model()->order_id)->supporter_order_id,
                    'content'   => 'Bạn có khiếu nại mới cho đơn hàng '.PurchaseOrder::find($form->model()->order_id)->order_number,
                    'status'    =>  1
                ]);
            }
        });

        return $form;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function formSubComment($id, $type)
    {
        $form = new Form(new ComplaintComment());

        $form->setAction(route('admin.complaints.addComment'));
        $form->summernote('content', 'Nội dung');
        $form->hidden('complaint_id')->default($id);
        $form->hidden('type')->default($type);
        
        $form->disableEditingCheck();
        $form->disableCreatingCheck();
        $form->disableViewCheck();

        $form->tools(function (Form\Tools $tools) {
            $tools->disableList();
        });

        Admin::style('
            .box-footer {
                padding: 0px !important;
                border: none !important;
            }
            .box {
                box-shadow: none;
            }
            .box.box-info {
                border: none;
            }
            .box-title, th {
                display: none !important;
            }
            // .grid-box .box-header {
            //     display: none !important;
            // }
        ');

        return $form;
    }

    public function addComment(Request $request) {
        $data = $request->all();
        
        $data['user_created_id'] = Admin::user()->id;
        ComplaintComment::create($data);

        if (Admin::user()->can('admin-handle-complaint')) {
            Complaint::find($data['complaint_id'])->update([
                'status'    =>  Complaint::PROCESS_NORMAL,
                'begin_handled_at'  =>  now()
            ]);

            $complaint = Complaint::find($data['complaint_id']);
            $order = PurchaseOrder::find($complaint->order_id);

            if (Admin::user()->isRole('sale_employee')) {
                $user_id = $order->supporter_order_id;
                $content = Admin::user()->name . " đã phản hồi khiếu nại đơn hàng ".$order->order_number;
            }
            else {
                $user_id = $order->supporter_sale_id;
                $content = Admin::user()->name . " đã phản hồi khiếu nại đơn hàng ".$order->order_number;
            }

            ComplaintNotification::create([
                'order_id'  =>  $order->id,
                'complaint_id'  =>  $data['complaint_id'],
                'user_id'   =>  $user_id,
                'content'   =>  $content,
                'status'    =>  1
            ]);
        }

        admin_toastr('Lưu thành công', 'success');
        return redirect()->back();
    }

    public function skipNotification($id) {
        ComplaintNotification::find($id)->update([
            'status'    =>  0
        ]);

        admin_toastr('Lưu thành công', 'success');
        return redirect()->back();
    }
}
