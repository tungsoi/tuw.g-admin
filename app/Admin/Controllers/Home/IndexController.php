<?php

namespace App\Admin\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\System\Alert;
use App\User;

class IndexController extends Controller
{
    public function index()
    {
        $alert = Alert::whereStatus(1)->first();
        return view('home.index', compact('alert'));
    }

    public function about()
    {
        return view('home.about');
    }

    public function proxy()
    {
        return view('home.proxy');
    }

    public function service()
    {
        return  view('home.service');
    }
}
