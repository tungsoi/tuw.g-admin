<?php

namespace App\Admin\Actions\TransportCode;

use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;

class ImportChinaReceive extends Action
{
    protected $selector = '.import-china-receive';

    public function handle(Request $request)
    {
        // $request ...

        return $this->response()->success('Success message...')->refresh();
    }

    public function form()
    {
        $this->file('file', 'Chọn File');
    }

    public function html()
    {
        return <<<HTML
        <a class="btn btn-sm btn-default import-post"><i class="fa fa-upload"></i>Import data</a>
HTML;
    }
}