<?php

namespace App\Admin\Services;

use App\Models\PaymentOrder\PaymentOrder;
use App\Models\TransportOrder\TransportCode;
use App\Models\PurchaseOrder\PurchaseOrder;
use App\Models\SaleReport\Report;
use App\User;

class SaleService
{
    protected $user;
    protected $user_id;
    protected $begin_date;
    protected $finish_date;

    public function __construct($user_id, $begin_date, $finish_date)
    {
        $this->user_id = $user_id;
        $this->begin_date = $begin_date." 00:00:00";
        $this->finish_date = $finish_date." 23:59:59";
        $this->user = User::find($user_id);
    }

    public function username() {
        return $this->user->name;
    }

    public function customers() {
        return User::where('staff_sale_id', $this->user_id)->select('id', 'wallet', 'created_at')->get();
    }

    public function newCustomers() {
        return $this->customers()->where('created_at', '>=', $this->begin_date)->where('created_at', '<=', $this->finish_date);
    }

    public function successOrder() {
        $ids = $this->customers()->pluck('id');
        return PurchaseOrder::whereStatus(9)
            ->whereIn('customer_id', $ids)
            ->where('deposited_at', '>=', $this->begin_date)
            ->where('deposited_at', '<=', $this->finish_date)
            ->where('success_at', '>=', $this->begin_date)
            ->where('success_at', '<=', $this->finish_date)
            ->get();
    }

    public function payment($ids = [], $orders) {
        if ($ids != null)
        {
            $orders = $orders->whereIn('customer_id', $ids);
        }

        $total = 0;
        if ($orders) {
            foreach ($orders as $order) {
                $total += str_replace(",", "", $order->amount()) * $order->current_rate;
            }
        }

        return round($total);
    }

    public function serviceFee($orders) {
        $total = 0;
        if ($orders) {
            foreach ($orders as $order) {
                $total += $order->purchase_order_service_fee * $order->current_rate;
            }
        }

        return round($total);
    }

    public function processingOrder() {
        $ids = $this->user->saleCustomers->pluck('id');
        $ordering_orders = PurchaseOrder::whereStatus(4)
        ->whereIn('customer_id', $ids)
        ->where('deposited_at', '>=', $this->begin_date)
        ->where('deposited_at', '<=', $this->finish_date)
        ->get();

        $ordered_orders = PurchaseOrder::whereIn('status', [5, 7])
        ->whereIn('customer_id', $ids)
        ->where('deposited_at', '>=', $this->begin_date)
        ->where('deposited_at', '<=', $this->finish_date)
        ->where('order_at', '>=', $this->begin_date)
        ->where('order_at', '<=', $this->finish_date)
        ->get();

        return $ordering_orders->merge($ordered_orders);
    }

    public function transportOrder($ids) {
        return PaymentOrder::whereIn('payment_customer_id', $ids)
            ->where('created_at', '>=', $this->begin_date)
            ->where('created_at', '<=', $this->finish_date)
            ->get();
    }

    public function weight($orders) {

        $total = 0;
        if ($orders->count() > 0) {
            foreach ($orders as $order) {

                $items = TransportCode::select('kg')->where('order_id', $order->id)->sum('kg');
                $total += $items;
                
            }
        }

        return $total;
    }


    public function transportFee($orders) {
        $total = 0;

        if ($orders->count() > 0) {
            foreach ($orders as $order) {
                $total += $order->final_total_price;
            }
        }

        return $total;
    }

    public function processingOrderPayment($ids = []) {
        if ($ids == null)
        {
            $orders = $this->processingOrder();
        } else {
            $orders = $this->processingOrder()->whereIn('customer_id', $ids);
        }

        $total = 0;
        if ($orders) {
            foreach ($orders as $order) {
                $total += $order->amount() * $order->current_rate;
            }
        }

        return round($total);
    }

    public function owedProcessingOrder() {

    }

    /**
     * Tổng tiền âm ví của tất cả khách hàng theo nhân viên Sale
     *
     * @return string
     */
    public function getTotalCustomerWallet(){
        return $this->user->saleCustomers->where('wallet', '<', 0)->sum('wallet');
    }

    /**
     * Tính tổng cân, khối, phí vận chuyển Alilogi
     *
     * @param string $key
     * @return string || array
     */
    public function getTotalTransport($key = "", $customers) {
        $total = [
            'kg'    =>  0,
            'total_transport_price' =>  0
        ];

        foreach ($customers as $customer) {
            $orders = PaymentOrder::where('payment_customer_id', $customer->id)
                ->where('created_at', '>=', $this->begin_date)
                ->where('created_at', '<=', $this->finish_date)
                ->get();

            foreach ($orders as $order) {
                foreach ($order->items as $item) {
                    $total['kg'] += $item->kg;
                }
                $total['total_transport_price'] += $order->final_total_price;
            }
        }

        return $total[$key];
    }

    public function getTotalWeight($customers) {
        return $this->getTotalTransport('kg', $customers);
    }

    public function getTotalCublicMeter($customers) {
        return $this->getTotalTransport('cublic_meter', $customers);
    }

    public function getTotalTransportPrice($customers) {
        return $this->getTotalTransport('total_transport_price',$customers);
    }
    /**
     * Tổng số lượng khách hàng mới
     *
     * @return object
     */
    public function getNewCustomer() {
        return $this->user->saleCustomers->where('created_at', '>=', $this->begin_date)->where('created_at', '<=', $this->finish_date);
    }

    /**
     * Danh sách đơn hàng dã hoàn thành
     *
     * @return object
     */
    public function getSuccessOrder() {
        $ids = $this->user->saleCustomers->pluck('id');
        return PurchaseOrder::whereStatus(9)
            ->whereIn('customer_id', $ids)
            ->where('deposited_at', '>=', $this->begin_date)
            ->where('deposited_at', '<=', $this->finish_date)
            ->where('success_at', '>=', $this->begin_date)
            ->where('success_at', '<=', $this->finish_date)
            ->orderBy('id', 'desc')
            ->get();
    }

     /**
     * Danh sách đơn hàng dã hoàn thành
     *
     * @return object
     */
    public function getNewOrder() {
        $ids = $this->user->saleCustomers->pluck('id');
        return PurchaseOrder::whereStatus(2)
            ->whereIn('customer_id', $ids)
            ->where('created_at', '>=', $this->begin_date)
            ->where('created_at', '<=', $this->finish_date)
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Danh sách đơn hàng chưa hoàn thành
     * đã cọc - đang đặt
     * đã đặt hàng
     * đã về việt nam
     *
     * @return object
     */
    public function getProcessingOrder() {
        $ids = $this->user->saleCustomers->pluck('id');
        $ordering_orders = PurchaseOrder::whereStatus(4)
        ->whereIn('customer_id', $ids)
        ->where('deposited_at', '>=', $this->begin_date)
        ->where('deposited_at', '<=', $this->finish_date)
        ->get();

        $ordered_orders = PurchaseOrder::whereIn('status', [5, 7])
        ->whereIn('customer_id', $ids)
        ->where('deposited_at', '>=', $this->begin_date)
        ->where('deposited_at', '<=', $this->finish_date)
        ->where('order_at', '>=', $this->begin_date)
        ->where('order_at', '<=', $this->finish_date)
        ->get();

        return $ordering_orders->merge($ordered_orders);
    }

    /**
     * Tính doanh số PaymentOrder
     *
     * @return string
     */
    public function getPaymentOrder() {
        $ids = $this->user->saleCustomers->pluck('id');
        $success_orders = PurchaseOrder::whereStatus(9)
            ->whereIn('customer_id', $ids)
            ->where('success_at', '>=', $this->begin_date)
            ->where('success_at', '<=', $this->finish_date)
            ->get();

        $total = 0;
        if ($success_orders) {
            foreach ($success_orders as $order) {
                $total += $order->amount() * $order->current_rate;
            }
        }

        $ordered_orders = PurchaseOrder::whereStatus(5)
            ->whereIn('customer_id', $ids)
            ->where('order_at', '>=', $this->begin_date)
            ->where('order_at', '<=', $this->finish_date)
            ->get();

        if ($ordered_orders) {
            foreach ($ordered_orders as $order) {
                $total += $order->amount() * $order->current_rate;
            }
        }

        return round($total);
    }

    /**
     * Tính tổng phí order
     *
     * @return string
     */
    public function getOrderServiceFee() {
        $success_orders = $this->getSuccessOrder();
        $orderd_orders = $this->getProcessingOrder();

        $all_orders = $success_orders->merge($orderd_orders);

        $total = 0;
        if ($all_orders) {
            foreach ($all_orders as $order) {
                $total += $order->purchase_order_service_fee * $order->current_rate;
            }
        }
        
        return round($total);
    }

    /**
     * Update báo cáo
     *
     * @param integer $id
     * @return boolean
     */
    public function updateCMD($id) {
        $report = Report::find($id);
        $report->status = "Đang thống kê dữ liệu ... ";
        $report->save();
        
        return true;
    }

    public function getSuccessOrderPayment() {
        $success_orders = $this->getSuccessOrder();
        
        $total = 0;
        if ($success_orders) {
            foreach ($success_orders as $order) {
                $total += $order->amount() * $order->current_rate;
            }
        }

        return round($total);
    }

    public function getProcessingOrderPayment() {
        $ordered_orders = $this->getProcessingOrder();
        $total = 0;

        if ($ordered_orders) {
            foreach ($ordered_orders as $order) {
                $total += $order->amount() * $order->current_rate;
            }
        }
        
        return round($total);
    }

    public function owed($total, $orders) {
        $deposited = 0;
        if ($orders) {
            foreach ($orders as $order) {
                $deposited += $order->deposited;
            }
        }
        
        return round($total - $deposited);
    }

    public function getSuccessOrderPaymentNewCustomer() {
        $new_customers = User::select('id')
            ->where('staff_sale_id', $this->user_id)
            ->where('created_at', '>=', $this->begin_date)
            ->where('created_at', '<=', $this->finish_date)
            ->whereIsActive(1)
            ->get()
            ->pluck('id');
        $success_orders = $this->getSuccessOrder()->whereIn('customer_id', $new_customers);

        $total = 0;
        if ($success_orders) {
            foreach ($success_orders as $order) {
                $total += $order->amount() * $order->current_rate;
            }
        }

        return round($total);
    }

    public function getProcessingOrderPaymentNewCustomer() {
        $new_customers = User::select('id')
            ->where('staff_sale_id', $this->user_id)
            ->where('created_at', '>=', $this->begin_date)
            ->where('created_at', '<=', $this->finish_date)
            ->whereIsActive(1)
            ->get()
            ->pluck('id');
        $orders = $this->getProcessingOrder()->whereIn('customer_id', $new_customers);

        $total = 0;
        if ($orders) {
            foreach ($orders as $order) {
                $total += $order->amount() * $order->current_rate;
            }
        }

        return round($total);
    }
}