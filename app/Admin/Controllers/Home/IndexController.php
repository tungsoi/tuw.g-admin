<?php

namespace App\Admin\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\System\Alert;
use App\User;

class IndexController extends Controller
{
    public function index()
    {
        $alert = Alert::first();
        return view('home.index', compact('alert'));
    }
}
