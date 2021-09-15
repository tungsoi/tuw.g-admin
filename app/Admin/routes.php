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
        'weight_portals'    =>  'System\\WeightPortalController',

        // customer
        'carts'                         =>  'Customer\\CartController',
        'customer_transactions'         =>  'Customer\\CustomerTransactionController',

        // purchase order
        'purchase_orders'       =>  'PurchaseOrder\\PurchaseOrderController',
        'purchase_order_items'  =>  'PurchaseOrder\\PurchaseOrderItemController',
        'offers'                =>  'PurchaseOrder\\OfferController',

        // transport order
        'transport_codes'   =>  'TransportOrder\\TransportCodeController',
        'china_receives'    =>  'TransportOrder\\ChinaReceiveController',
        'vietnam_receives'    =>  'TransportOrder\\VietnamReceiveController'
    ]);

    // transport order
    $router->get('transport_codes/seach/{transport_code}', 'TransportOrder\\TransportCodeController@search')->name('transport_codes.search');

    $router->get('china_receives', 'TransportOrder\\ChinaReceiveController@indexRebuild')->name('china_receives.index');
    $router->post('china_receives', 'TransportOrder\\ChinaReceiveController@storeRebuild')->name('china_receives.store');

    $router->get('vietnam_receives', 'TransportOrder\\VietnamReceiveController@indexRebuild')->name('vietnam_receives.index');
    $router->get('vietnam_receives/search/{transport_code}', 'TransportOrder\\VietnamReceiveController@search')->name('vietnam_receives.search');
    $router->post('vietnam_receives', 'TransportOrder\\VietnamReceiveController@storeRebuild')->name('vietnam_receives.store');

    // payment order
    $router->get('payments/{ids}', 'TransportOrder\\PaymentController@indexRebuild')->name('payments.index');
    $router->post('payments', 'TransportOrder\\PaymentController@storeRebuild')->name('payments.storeRebuild');
    $router->get('payment_orders/all', 'TransportOrder\\PaymentController@indexAll')->name('payments.all');
    $router->post('payment_orders/export_order', 'TransportOrder\\PaymentController@exportOrder')->name('payments.exportOrder');
    $router->get('payment_orders/{id}/detail', 'TransportOrder\\PaymentController@showRebuild')->name('payments.showRebuild');


    // transaction
    $router->get('transactions/duplicate', 'System\\TransactionController@detail')->name('transactions.duplicate');

    // customer
    $router->get('customers/{id}/transactions', 'System\\CustomerController@transaction')->name('customers.transactions');
    $router->post('customers/storeRecharge', 'System\\CustomerController@storeRecharge')->name('customers.storeRecharge');
    $router->post('customers/updateRecharge', 'System\\CustomerController@updateRecharge')->name('customers.updateRecharge');
    $router->get('customers/{id}/walletWeight', 'System\\CustomerController@walletWeight')->name('customers.walletWeight');
    $router->post('customers/storeRechargeWeight', 'System\\CustomerController@storeRechargeWeight')->name('customers.storeRechargeWeight');
    $router->post('customers/updateRechargeWeght', 'System\\CustomerController@updateRechargeWeght')->name('customers.updateRechargeWeght');
    $router->get('customers/{id}/find', 'System\\CustomerController@find')->name('customers.find');

    $router->post('customer_purchase_orders/storeFromCart', 'Customer\\CustomerPurchaseOrderController@storeFromCart')->name('customer_purchase_orders.storeFromCart');

    // purchase order
    $router->post('purchase_orders/customer_deposite', 'PurchaseOrder\\PurchaseOrderController@customerDeposite')->name('purchase_orders.customer_deposite');
    $router->get('purchase_orders/{id}/admin_deposite', 'PurchaseOrder\\PurchaseOrderController@adminDeposite')->name('purchase_orders.admin_deposite');
    $router->post('purchase_orders/post_admin_deposite', 'PurchaseOrder\\PurchaseOrderController@postAdminDeposite')->name('purchase_orders.post_admin_deposite');
    $router->post('purchase_orders/confirm_ordered', 'PurchaseOrder\\PurchaseOrderController@postConfirmOrdered')->name('purchase_orders.confirm_ordered');
    $router->get('purchase_orders/{id}/edit_data', 'PurchaseOrder\\PurchaseOrderController@editData')->name('purchase_orders.edit_data');
    $router->post('purchase_orders/store_edit_data', 'PurchaseOrder\\PurchaseOrderController@postEditData')->name('purchase_orders.store_edit_data');
   
    // weight portal
    $router->get('weight_portals', 'System\\WeightPortalController@indexRebuild')->name('weight_portals.index'); 
    
    // carts
    $router->get('/carts/booking/{ids}', 'Customer\\CartController@booking')->name('carts.booking');
    $router->post('/carts/storeAdd1688', 'Customer\\CartController@storeAdd1688')->name('carts.storeAdd1688');

    // purchase_order_items
    $router->get('search_items/{transport_code}', 'PurchaseOrder\\PurchaseOrderItemController@showRebuild')->name('purchase_order_items.showRebuild');

});
