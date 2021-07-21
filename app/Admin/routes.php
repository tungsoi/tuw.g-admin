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

    $router->get('register', 'RegisterController@index')->name('register');
    $router->post('register', 'RegisterController@register')->name('register');

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
        'transactions'      =>  'System\\TransactionController',

        // customer
        'carts'                         =>  'Customer\\CartController',
        'customer_purchase_orders'      =>  'Customer\\CustomerPurchaseOrderController',
        'customer_transactions'         =>  'Customer\\CustomerTransactionController',

        // purchase order
        'purchase_orders'   =>  'PurchaseOrder\\PurchaseOrderController'
    ]);

    // transport order
    $router->get('transport_codes/seach/{transport_code}', 'TransportOrder\\TransportCodeController@search')->name('transport_codes.search');

    $router->get('china_receives', 'TransportOrder\\ChinaReceiveController@index')->name('china_receives');
    $router->post('china_receives/storeChinaReceive', 'TransportOrder\\ChinaReceiveController@storeTransportCode')->name('china_receives.storeChinaReceive');

    $router->get('vietnam_receives', 'TransportOrder\\VietnamReceiveController@index')->name('vietnam_receives');
    $router->post('vietnam_receives/storeVietnamReceive', 'TransportOrder\\VietnamReceiveController@storeTransportCode')->name('vietnam_receives.storeChinaReceive');

    // transaction
    $router->get('transactions/duplicate', 'System\\TransactionController@detail')->name('transactions.duplicate');

    // customer
    $router->get('customers/{id}/transactions', 'System\\CustomerController@transaction')->name('customers.transactions');
    $router->post('customers/storeRecharge', 'System\\CustomerController@storeRecharge')->name('customers.storeRecharge');
    $router->post('customers/updateRecharge', 'System\\CustomerController@updateRecharge')->name('customers.updateRecharge');
    $router->get('customers/{id}/walletWeight', 'System\\CustomerController@walletWeight')->name('customers.walletWeight');

    $router->post('customer_purchase_orders/storeFromCart', 'Customer\\CustomerPurchaseOrderController@storeFromCart')->name('customer_purchase_orders.storeFromCart');

    // purchase order
    $router->post('purchase_orders/addTransportCode', 'PurchaseOrder\\PurchaseOrderController@addTransportCode')->name('purchase_orders.addTransportCode');

    
});
