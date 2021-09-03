<?php

namespace App\Admin\Actions\Core;

use Encore\Admin\Admin;

class BtnView
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
        //
    }

    protected function render()
    {
        return '<a href="'.$this->url.'" data-url="'.$this->url.'" data-id="'.$this->id.'" class="grid-row-view btn btn-xs btn-primary" data-toggle="tooltip" title="Xem chi tiết">
                <i class="fa fa-eye"></i>
            </a>';
    }

    public function __toString()
    {
        return $this->render();
    }
}