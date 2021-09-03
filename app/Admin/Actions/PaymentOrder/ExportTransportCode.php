<?php

namespace App\Admin\Actions\PaymentOrder;

use Encore\Admin\Admin;

class ExportTransportCode
{
    protected $id;
    protected $url;

    public function __construct($id, $url)
    {
        $this->id = $id;
        $this->url = $url;
    }

    protected function script()
    {
        return <<<SCRIPT

        $('.grid-row-export-order').on('click', function () {

            let url = $(this).data('url');
            let id = $(this).data('id');

            Swal.fire({
                title: 'Bạn có chắc chắn muốn xuất kho đơn hàng này ?',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Đồng ý',
                cancelButtonText: 'Huỷ bỏ'
            }).then((result) => {
                $('.loading-overlay').show();
                if (result.value == true && result.dismiss == undefined) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });

                    $.ajax(
                    {
                        url: url,
                        type: 'post', // replaced from put
                        dataType: "JSON",
                        data: {
                            'order_id': id
                        },
                        success: function (response)
                        {
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

                $('.loading-overlay').hide();
            })

        });

SCRIPT;
    }

    protected function render()
    {
        Admin::script($this->script());

        return '<a href="javascript:void(0);" data-url="'.$this->url.'" data-id="'.$this->id.'" class="grid-row-export-order btn btn-xs btn-success" data-toggle="tooltip" title="Xuất kho cả đơn">
                <i class="fa fa-check"></i>
            </a>';
    }

    public function __toString()
    {
        return $this->render();
    }
}