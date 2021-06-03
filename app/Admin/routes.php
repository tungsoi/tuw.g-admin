<?php

use Illuminate\Routing\Router;

// Dashboard
Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {

    $router->get('', function() {
        return view('dashboard.index');
    });

});


// Admin
Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {

    $router->get('', 'HomeController@index')->name('home');
    $router->resources([
        'auth/users'    =>  'UserController',
    ]);
});
