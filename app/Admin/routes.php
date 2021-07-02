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

        // system
        'auth/users'        =>  'System\\UserController',
        'auth/roles'        =>  'System\\RoleController',
        'warehouses'        =>  'System\\WarehouseController',
        'exchange_rates'    =>  'System\\ExchangeRateController',
        'alerts'            =>  'System\\AlertController',
        'transport_lines'   =>  'System\\TransportLineController',
        'customers'         =>  'System\\CustomerController',
        'transactions'      =>  'System\\TransactionController'
    ]);


    // transport order
    $router->get('china_receives', 'TransportOrder\\ChinaReceiveController@index')->name('china_receives');
    $router->post('china_receives/save', 'TransportOrder\\ChinaReceiveController@save');

    $router->get('vietnam_receives', 'TransportOrder\\VietnamReceiveController@index')->name('vietnam_receives');
    $router->get('transactions/duplicate', 'System\\TransactionController@detail')->name('transactions.duplicate');

    // customer
    $router->get('customers/{id}/transactions', 'System\\CustomerController@transaction')->name('customers.transactions');
    $router->post('customers/storeRecharge', 'System\\CustomerController@storeRecharge')->name('customers.storeRecharge');
    $router->post('customers/updateRecharge', 'System\\CustomerController@updateRecharge')->name('customers.updateRecharge');
});
