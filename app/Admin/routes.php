<?php

use Illuminate\Routing\Router;

// Dashboard
Route::group([
    'prefix'        => '',
    'namespace'     => 'App\\Admin\\Controllers\\Home',
    'middleware'    => ['web'],
    'as'            => 'home.',
], function (Router $router) {

    $router->get(
        '/', 'IndexController@index'
    )->name('index');

});


// Admin
Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {

    $router->get('', 'System\\HomeController@index')->name('home');
    $router->resources([
        'auth/users'        =>  'System\\UserController',
        'auth/roles'        =>  'System\\RoleController',
        'warehouses'        =>  'System\\WarehouseController',
        'exchange_rates'    =>  'System\\ExchangeRateController',
        'alerts'            =>  'System\\AlertController',
        'transport_lines'   =>  'System\\TransportLineController',
        'customers'         =>  'System\\CustomerController'
    ]);
});
