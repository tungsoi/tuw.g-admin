<?php

namespace App\Admin\Actions\Core;

use Encore\Admin\Admin;

class BtnDelete
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

        $('.grid-row-custom-delete').on('click', function () {

            let url = $(this).data('url');
            let id = $(this).data('id');

            Swal.fire({
                title: 'Bạn có chắc chắn muốn xoá?',
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
                        type: 'delete', // replaced from put
                        dataType: "JSON",
                        success: function (response)
                        {
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

        return '<a href="javascript:void(0);" data-url="'.$this->url.'" data-id="'.$this->id.'" class="grid-row-custom-delete btn btn-xs btn-danger" data-toggle="tooltip" title="Xóa">
                <i class="fa fa-trash"></i>
            </a>';
    }

    public function __toString()
    {
        return $this->render();
    }
}