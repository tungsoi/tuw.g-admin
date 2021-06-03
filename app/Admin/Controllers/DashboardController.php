<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\User;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\InfoBox;

class DashboardController extends Controller
{
    public function index(Content $content)
    {
        return view('dashboard.index');
    }
}
