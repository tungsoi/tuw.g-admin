<?php

namespace App\Admin\Controllers\Home;

use App\Http\Controllers\Controller;
use App\User;

class IndexController extends Controller
{
    public function index()
    {
        return view('home.index');
    }
}
