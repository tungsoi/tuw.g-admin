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
        '/',
        'IndexController@index'
    )->name('index');

    $router->get('about', 'IndexController@about')->name('about');
    $router->get('proxy', 'IndexController@proxy')->name('proxy');

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
    'middleware'    =>  [
        'web',
        'admin',
        'customer_informations',
        'admin.permission:deny,customer'
    ],
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {
    $router->resources([
        'auth/roles'        =>  'System\\RoleController', // admin
        'warehouses'        =>  'System\\WarehouseController', // admin
        'exchange_rates'    =>  'System\\ExchangeRateController', // admin
        'alerts'            =>  'System\\AlertController', // admin
        'transport_lines'   =>  'System\\TransportLineController', // admin
        'customers'         =>  'System\\CustomerController', // admin
        'transactions'      =>  'System\\TransactionController', // admin
        'weight_portals'    =>  'System\\WeightPortalController', // admin
        'team_sales'        =>  'System\\TeamSaleController', // admin
        'offers'                =>  'PurchaseOrder\\OfferController', // admin
        'complaints'            =>  'PurchaseOrder\\ComplaintController', // admin
        'china_receives'    =>  'TransportOrder\\ChinaReceiveController', // admin
        'vietnam_receives'    =>  'TransportOrder\\VietnamReceiveController', // admin
        'report_warehouses'     =>  'ReportWarehouse\\DetailController', // admin
        'report_warehouse_portal'     =>  'ReportWarehouse\\PortalController', // admin
        'report_warehouse_daily'     =>  'ReportWarehouse\\DailyController', // admin
        'revenue_reports'      =>  'Report\\SaleReportController', // admin,
        'revenue_report_fetchs'            =>  'Report\\FetchController',
        'vn_customer_code'     =>   'TransportOrder\\VietnamCustomerCodeController',
        'revenue_warehouses'    =>  'Report\\RevenueWarehouseController',
        'banks'     =>  'System\\BankController',
        'banking_check' =>  'Report\\BankingCheckController',
        'financial_reports' =>  'Report\\FinancialReportController',
        'purchase_order_today'  =>  'PurchaseOrder\\TodayController',
        'payment_order_zero'    =>  'TransportOrder\\PaymentOrderZeroController',
        'conflict_transport_code'   =>  'Report\\ConflictTransportCodeController',
        'tracking_payment_orders'   =>  'Report\\TrackingPaymentOrderController',
        'sale_salary_details'   =>  'Report\\SaleSalaryDetailController',
        'order_reports' =>  'Report\\OrderReportController',
        'order_report_success'  =>  'Report\\OrderReportSuccessController',
        'ars/categories'    =>  'ReportAr\\CategoryController',
        'ars/units'     =>  'ReportAr\\UnitController',
        'ars/details'   =>  'ReportAr\\DetailController',
        'weight_portals_company'    =>  'System\\WeightPortalCompanyController', // admin
        'weight_portals_staff'  =>  'System\\WeightPortalStaffController', // admin
        'weight_portals_customer'   =>  'System\\WeightPortalCustomerController',
        'weight_portals_payment'    =>  'System\\WeightPortalPaymentController',
    ]);

    $router->get('ar_reports/{ar_report}', 'Report\\ArReportController@showRebuild')->name('ar_reports.show');

    $router->get('report_portals', 'Report\\PortalController@indexRebuild')->name('report_portals');
    $router->get('report_portals/calculatorEstimateAmountBooking', 'Report\\PortalController@calculatorEstimateAmountBooking')->name('report_portals.calculatorEstimateAmountBooking');

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
    $router->get('customers/{id}/calculator_wallet', 'System\\CustomerController@calculator_wallet')->name('customers.calculator_wallet');
    $router->post('customers/update_wallet', 'System\\CustomerController@update_wallet')->name('customers.update_wallet');

    $router->get('china_receives', 'TransportOrder\\ChinaReceiveController@indexRebuild')->name('china_receives.index');
    $router->post('china_receives', 'TransportOrder\\ChinaReceiveController@storeRebuild')->name('china_receives.store');

    $router->get('vietnam_receives', 'TransportOrder\\VietnamReceiveController@indexRebuild')->name('vietnam_receives.index');
    $router->get('vietnam_receives/search/{transport_code}', 'TransportOrder\\VietnamReceiveController@search')->name('vietnam_receives.search');
    $router->post('vietnam_receives', 'TransportOrder\\VietnamReceiveController@storeRebuild')->name('vietnam_receives.store');

    // payment order
    $router->get('payments/{ids}', 'TransportOrder\\PaymentController@indexRebuild')->name('payments.index');
    $router->post('payments', 'TransportOrder\\PaymentController@storeRebuild')->name('payments.storeRebuild');

    // weight portal
    $router->get('weight_portals', 'System\\WeightPortalController@indexRebuild')->name('weight_portals.index');

    // complaint

    $router->get('/complaints/{complaint}', 'PurchaseOrder\\ComplaintController@showComplaint')->name('complaints.showComplaint');
    $router->post('/complaints/addComment', 'PurchaseOrder\\ComplaintController@addComment')->name('complaints.addComment');
    $router->get('/complaints/skipNotification/{id}', 'PurchaseOrder\\ComplaintController@skipNotification')->name('complaints.skipNotification');

    $router->post('/complaints/adminConfirmSuccess', 'PurchaseOrder\\ComplaintController@storeAdminConfirmSuccess')->name('complaints.storeAdminConfirmSuccess');
    $router->post('/complaints/customerConfirmSuccess', 'PurchaseOrder\\ComplaintController@storeCustomerConfirmSuccess')->name('complaints.storeCustomerConfirmSuccess');

    // weight report
    $router->post('/report_warehouses/storeDetail', 'ReportWarehouse\\DetailController@storeDetail')->name('report_warehouses.storeDetail');
    $router->put('/report_warehouses/{report_warehouse}', 'ReportWarehouse\\DetailController@updateDetail')->name('report_warehouses.updateDetail');

    $router->get('/compare_customer_wallet', 'Report\\CustomerWalletController@index')->name('report.compare_customer_wallet');

    $router->post('payment_orders/cancel', 'TransportOrder\\PaymentController@cancel')->name('payments.cancel');
    $router->get('purchase_orders/get-list-customer-new-order', 'PurchaseOrder\\PurchaseOrderController@getListCustomerNewOrder')->name('purchase_orders.geListCustomerNewOrder');
    $router->get('purchase_orders/get-list-customer-depositting-order', 'PurchaseOrder\\PurchaseOrderController@getListCustomerDeposittingOrder')->name('purchase_orders.geListCustomerNewOrder');
    $router->get('revenue_reports/{revenue_reports_id}/detech', 'Report\\SaleReportController@detech')->name('revenue_reports.detech');
    $router->get('revenue_reports/{revenue_reports_id}/salary', 'Report\\SaleReportController@salary')->name('revenue_reports.salary');
});

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {
    $router->get('', 'System\\HomeController@blank')->name('blank');
    $router->get('home', 'System\\HomeController@index')->name('home');
    $router->resources([

        // system
        'auth/users'        =>  'System\\UserController',

        // customer
        'carts'                         =>  'Customer\\CartController',
        'customer_transactions'         =>  'Customer\\CustomerTransactionController',

        // purchase order
        'purchase_orders'       =>  'PurchaseOrder\\PurchaseOrderController',
        'purchase_order_items'  =>  'PurchaseOrder\\PurchaseOrderItemController',

        // transport order
        'transport_codes'   =>  'TransportOrder\\TransportCodeController',
    ]);

    // transport order
    $router->get('transport_codes/seach/{transport_code}', 'TransportOrder\\TransportCodeController@search')->name('transport_codes.search');

    $router->get('payment_orders/all', 'TransportOrder\\PaymentController@indexAll')->name('payments.all');
    $router->post('payment_orders/export_order', 'TransportOrder\\PaymentController@exportOrder')->name('payments.exportOrder');
    $router->get('payment_orders/{id}/detail', 'TransportOrder\\PaymentController@showRebuild')->name('payments.showRebuild');

    $router->post('customer_purchase_orders/storeFromCart', 'Customer\\CustomerPurchaseOrderController@storeFromCart')->name('customer_purchase_orders.storeFromCart');

    // purchase order
    $router->post('purchase_orders/customer_deposite', 'PurchaseOrder\\PurchaseOrderController@customerDeposite')->name('purchase_orders.customer_deposite');
    $router->get('purchase_orders/{id}/admin_deposite', 'PurchaseOrder\\PurchaseOrderController@adminDeposite')->name('purchase_orders.admin_deposite');
    $router->post('purchase_orders/post_admin_deposite', 'PurchaseOrder\\PurchaseOrderController@postAdminDeposite')->name('purchase_orders.post_admin_deposite');
    $router->post('purchase_orders/confirm_ordered', 'PurchaseOrder\\PurchaseOrderController@postConfirmOrdered')->name('purchase_orders.confirm_ordered');
    $router->get('purchase_orders/{id}/edit_data', 'PurchaseOrder\\PurchaseOrderController@editData')->name('purchase_orders.edit_data');
    $router->post('purchase_orders/store_edit_data', 'PurchaseOrder\\PurchaseOrderController@postEditData')->name('purchase_orders.store_edit_data');
    $router->post('purchase_orders/post_admin_deposite_multiple', 'PurchaseOrder\\PurchaseOrderController@postAdminDepositeMultiple')->name('purchase_orders.post_admin_deposite_multiple');
    $router->post('purchase_orders/post_customer_deposite_multiple', 'PurchaseOrder\\PurchaseOrderController@postCustomerDepositeMultiple')->name('purchase_orders.post_admin_deposite_multiple');
    $router->get('purchase_orders/admin_deposite_multiple/{ids}', 'PurchaseOrder\\PurchaseOrderController@getAdminDepositeMultiple')->name('purchase_orders.admin_deposite_multiple');
    $router->post('purchase_orders/submit_admin_deposite_multiple', 'PurchaseOrder\\PurchaseOrderController@submitAdminDepositeMultiple')->name('purchase_orders.submit_admin_deposite_multiple');
    $router->get('purchase_orders/{purchase_orders}/calculate_items', 'PurchaseOrder\\PurchaseOrderController@calculateItems')->name('purchase_orders.calculate_items');
    $router->get('purchase_orders/{purchase_orders}/calculate_item_by_status', 'PurchaseOrder\\PurchaseOrderController@calculateItemsByStatus')->name('purchase_orders.calculate_item_by_status');
    $router->get('customers/{customers}/calculate_customer_wallet', 'System\\CustomerController@getCustomerWallet')->name('customers.calculate_customer_wallet');

    // carts
    $router->get('/carts/booking/{ids}', 'Customer\\CartController@booking')->name('carts.booking');
    $router->post('/carts/storeAdd1688', 'Customer\\CartController@storeAdd1688')->name('carts.storeAdd1688');

    // purchase_order_items
    $router->get('search_items/{transport_code}', 'PurchaseOrder\\PurchaseOrderItemController@showRebuild')->name('purchase_order_items.showRebuild');
    $router->post('vn_received', 'PurchaseOrder\\PurchaseOrderItemController@vnReceived')->name('purchase_order_items.vnReceived');
    $router->post('updateTransportCode', 'PurchaseOrder\\PurchaseOrderController@updateTransportCode')->name('purchase_orders.updateTransportCode');
});


Route::post('api/cart/create', 'App\\Admin\\Controllers\\Customer\\CartController@createProduct');