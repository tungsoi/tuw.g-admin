<?php

namespace App\Admin\Controllers\Home;

use App\Http\Controllers\Controller;
use App\Models\System\Alert;
use App\Models\System\Service;
use App\User;
use Illuminate\Support\Str;

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
        $services = Service::all();
        $data = [];
        foreach ($services as $item) {
            $data[] = [
                'title' => $item['title'] ?? '',
                'created_at' =>  $item['created_at'] ? date_format($item['created_at'], "Y/m/d") : '',
                'description' => $item['description'] ? Str::limit($item['description'], 150) : '',
                'img' => $item['image'] ? $item['image'][0] : ''
            ];
        }
        return  view('home.service', compact('data'));
    }
}
