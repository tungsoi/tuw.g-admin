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
    $router->post('register', 'RegisterController@register')->name('postRegister');

    $router->get('forgotPassword', 'ForgotPasswordController@getForgotPassword')->name('getForgotPassword');
    $router->post('postForgotPassword', 'ForgotPasswordController@postForgotPassword')->name('postForgotPassword');
    $router->get('getVerifyForgotPassword', 'ForgotPasswordController@getVerifyForgotPassword')->name('getVerifyForgotPassword');
    $router->post('postVerifyForgotPassword', 'ForgotPasswordController@postVerifyForgotPassword')->name('postVerifyForgotPassword');
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
        'customer_transactions'         =>  'Customer\\CustomerTransactionController',

        // purchase order
        'purchase_orders'   =>  'PurchaseOrder\\PurchaseOrderController',
        'purchase_order_items'  =>  'PurchaseOrder\\PurchaseOrderItemController',

        // transport order
        'transport_codes'  =>  'TransportOrder\\TransportCodeController'
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
    $router->post('customers/storeRechargeWeight', 'System\\CustomerController@storeRechargeWeight')->name('customers.storeRechargeWeight');
    $router->post('customers/updateRechargeWeght', 'System\\CustomerController@updateRechargeWeght')->name('customers.updateRechargeWeght');

    $router->post('customer_purchase_orders/storeFromCart', 'Customer\\CustomerPurchaseOrderController@storeFromCart')->name('customer_purchase_orders.storeFromCart');

    // purchase order
    $router->post('purchase_orders/customer_deposite', 'PurchaseOrder\\PurchaseOrderController@customerDeposite')->name('purchase_orders.customer_deposite');
    
});
