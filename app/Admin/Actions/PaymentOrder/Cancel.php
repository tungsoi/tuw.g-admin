<?php

namespace App\Admin\Actions\PaymentOrder;

use Encore\Admin\Admin;

class Cancel
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    protected function script()
    {
        return <<<SCRIPT

        $('.grid-row-cancel-order').on('click', function () {

            let id = $(this).data('id');

            Swal.fire({
                title: 'Bạn có chắc chắn muốn huỷ đơn hàng này ?',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Đồng ý',
                cancelButtonText: 'Huỷ bỏ'
            }).then((result) => {
                if (result.value == true && result.dismiss == undefined) {

                    $('.loading-overlay').show();
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $.ajax(
                    {
                        url: '/admin/payment_orders/cancel',
                        type: 'post', // replaced from put
                        dataType: "JSON",
                        data: {
                            'order_id': id
                        },
                        success: function (response)
                        {

                            $('.loading-overlay').hide();
                            if (response.status) {
                                $.admin.toastr.success(response.message, '', {positionClass: 'toast-top-center'});
                            } else {
                                $.admin.toastr.error(response.message, '', {positionClass: 'toast-top-center'});
                            }

                            if (response.isRedirect) {
                                setTimeout(function () {
                                    window.location.href = response.url;
                                }, 1000);
                            } else {
                                setTimeout(function () {
                                    window.location.reload();
                                }, 1000);
                            }
                        }
                    });
                }
            })

        });

SCRIPT;
    }

    protected function render()
    {
        Admin::script($this->script());

        return '<a href="javascript:void(0);" data-id="'.$this->id.'" class="grid-row-cancel-order btn btn-xs btn-danger" data-toggle="tooltip" title="Huỷ đơn">
                <i class="fa fa-trash"></i>
            </a>';
    }

    public function __toString()
    {
        return $this->render();
    }
}