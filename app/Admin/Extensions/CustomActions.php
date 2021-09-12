<?php

namespace App\Admin\Extensions;

use Encore\Admin\Admin;
use Encore\Admin\Grid\Displayers\Actions;

class CustomActions extends Actions
{

    /**
     * Render view action.
     *
     * @return string
     */
    protected function renderView()
    {
        return <<<EOT
<a href="{$this->getResource()}/{$this->getRouteKey()}" class="{$this->grid->getGridRowName()}-view btn btn-xs btn-primary" data-toggle="tooltip" title="Xem chi tiết" >
    <i class="fa fa-eye"></i>
</a>
EOT;
    }

    /**
     * Render edit action.
     *
     * @return string
     */
    protected function renderEdit()
    {
        return <<<EOT
<a href="{$this->getResource()}/{$this->getRouteKey()}/edit" class="{$this->grid->getGridRowName()}-edit btn btn-xs btn-warning" data-toggle="tooltip" title="Chỉnh sửa" >
    <i class="fa fa-edit"></i>
</a>
EOT;
    }

    /**
     * Render delete action.
     *
     * @return string
     */
    protected function renderDelete()
    {
        $this->setupDeleteScript();

        return <<<EOT
<a href="javascript:void(0);" data-id="{$this->getKey()}" class="{$this->grid->getGridRowName()}-delete btn btn-xs btn-danger" data-toggle="tooltip" title="Xóa">
    <i class="fa fa-trash"></i>
</a>
EOT;
    }
}
