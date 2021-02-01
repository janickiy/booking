<?php

Route::get('/login', 'AuthController@showLoginForm')->name('admin.login');
Route::post('/login', 'AuthController@login')->name('admin.login.submit');
Route::get('logout/', 'AuthController@logout')->name('admin.logout');
Route::get('/', 'IndexController@index')->name('admin.index');

Route::group(['prefix' => 'orders'], function () {

    Route::get('', 'OrdersController@list')->name('admin.orders.list');
    Route::post('create', 'OrdersController@store')->name('admin.orders.store');

    Route::group(['prefix' => 'orders-railway'], function () {
        Route::get('info/{id}', 'OrdersController@railwayInfo')->name('admin.ordersrailway.info')->where('id', '[0-9]+');
    });

    Route::group(['prefix' => 'orders_aeroexpress'], function () {
        Route::get('info/{id}', 'OrdersController@aeroexpressInfo')->name('admin.orderaeroexpress.info')->where('id', '[0-9]+');
    });

    Route::group(['prefix' => 'orders_avia'], function () {
        Route::get('info/{id}', 'OrdersAviaController@info')->name('admin.orderavia.info')->where('id', '[0-9]+');
    });

    Route::group(['prefix' => 'orders_bus'], function () {
        Route::get('info/{id}', 'OrdersBusController@info')->name('admin.orderbus.info')->where('id', '[0-9]+');
    });
});

// жд
Route::group(['prefix' => 'railway'], function () {

    Route::group(['prefix' => 'trains'], function () {
        Route::get('', 'TrainsController@list')->name('admin.trains.list');
        Route::get('create', 'TrainsController@create')->name('admin.trains.create');
        Route::post('store', 'TrainsController@store')->name('admin.trains.store');
        Route::get('edit/{id}', 'TrainsController@edit')->name('admin.trains.edit')->where('id', '[0-9]+');
        Route::put('update', 'TrainsController@update')->name('admin.trains.update');
        Route::delete('destroy/{id}', 'TrainsController@destroy')->name('admin.trains.destroy')->where('id', '[0-9]+');
    });

    Route::group(['prefix' => 'trains-car'], function () {
        Route::get('', 'TrainsCarController@list')->name('admin.trainscar.list');
        Route::get('create', 'TrainsCarController@create')->name('admin.trainscar.create');
        Route::post('store', 'TrainsCarController@store')->name('admin.trainscar.store');
        Route::get('edit/{id}', 'TrainsCarController@edit')->name('admin.trainscar.edit')->where('id', '[0-9]+');
        Route::put('update', 'TrainsCarController@update')->name('admin.trainscar.update');
        Route::delete('destroy/{id}', 'TrainsCarController@destroy')->name('admin.trainscar.destroy')->where('id', '[0-9]+');
        Route::delete('del-image/{id}', 'TrainsCarController@delImage')->name('admin.trainscar.delimage')->where('id', '[0-9]+');
    });

    Route::group(['prefix' => 'stations'], function () {
        Route::get('', 'RailwayStationsController@list')->name('admin.stations.list');
        Route::get('edit/{id}', 'RailwayStationsController@edit')->name('admin.stations.edit')->where('id', '[0-9]+');
        Route::put('update', 'RailwayStationsController@update')->name('admin.stations.update');
    });
});

//управление контентом
Route::group(['prefix' => 'pages'], function () {

    // страницы и разделы
    Route::get('/', 'PagesController@list')->name('admin.pages.list');
    Route::get('create', 'PagesController@create')->name('admin.pages.create');
    Route::post('store', 'PagesController@store')->name('admin.pages.store');
    Route::get('edit/{id}', 'PagesController@edit')->name('admin.pages.edit')->where('id', '[0-9]+');
    Route::put('update', 'PagesController@update')->name('admin.pages.update');
    Route::delete('destroy/{id}', 'PagesController@destroy')->name('admin.pages.destroy')->where('id', '[0-9]+');

    // меню
    Route::group(['prefix' => 'menu'], function () {
        Route::get('', 'MenuController@list')->name('admin.menu.list');
        Route::get('create/{parent_id?}', 'MenuController@create')->name('admin.menu.create');
        Route::post('store', 'MenuController@store')->name('admin.menu.store');
        Route::get('edit/{id}', 'MenuController@edit')->name('admin.menu.edit')->where('id', '[0-9]+');
        Route::put('update', 'MenuController@update')->name('admin.menu.update');
        Route::get('delete/{id}', 'MenuController@destroy')->name('admin.menu.delete')->where('id', '[0-9]+');
    });

    // офисы
    Route::group(['prefix' => 'offices'], function () {
        Route::get('', 'OfficesController@list')->name('admin.offices.list');
        Route::get('create', 'OfficesController@create')->name('admin.offices.create');
        Route::post('store', 'OfficesController@store')->name('admin.offices.store');
        Route::get('edit/{id}', 'OfficesController@edit')->name('admin.offices.edit')->where('id', '[0-9]+');
        Route::put('update', 'OfficesController@update')->name('admin.offices.update');
        Route::delete('destroy/{id}', 'OfficesController@destroy')->name('admin.offices.destroy')->where('id', '[0-9]+');
    });
});

Route::group(['prefix' => 'hotel'], function () {

    //Отели
    Route::group(['prefix' => 'hotel'], function () {
        Route::get('/', 'HotelController@list')->name('admin.hotel.list');
        Route::get('edit/{id}', 'HotelController@edit')->name('admin.hotel.edit')->where('id', '[0-9]+');
        Route::put('update', 'HotelController@update')->name('admin.hotel.update');
        Route::delete('destroy/{id}', 'HotelController@destroy')->name('admin.hotel.destroy')->where('id', '[0-9]+');
    });

    //Атребуты отелей
    Route::group(['prefix' => 'attributes'], function () {
        Route::get('/', 'HotelsAttributesController@list')->name('admin.hotels_attributes.list');
        Route::get('create', 'HotelsAttributesController@create')->name('admin.hotels_attributes.create');
        Route::post('store', 'HotelsAttributesController@store')->name('admin.hotels_attributes.store');
        Route::get('edit/{id}', 'HotelsAttributesController@edit')->name('admin.hotels_attributes.edit')->where('id', '[0-9]+');
        Route::put('update', 'HotelsAttributesController@update')->name('admin.hotels_attributes.update');
        Route::delete('destroy/{id}/{type}/{code}', 'HotelsAttributesController@destroy')->name('admin.hotels_attributes.destroy')->where('id', '[0-9]+');
    });

    // поставщик атрибутов
    Route::group(['prefix' => 'attributes_providers'], function () {
        Route::get('/{attribute_id}', 'HotelsAttributesProvidersController@list')->name('admin.hotels_attributes_providers.list')->where('attribute_id', '[0-9]+');
        Route::get('create/{attribute_id}', 'HotelsAttributesProvidersController@create')->name('admin.hotels_attributes_providers.create')->where('attribute_id', '[0-9]+');
        Route::post('store', 'HotelsAttributesProvidersController@store')->name('admin.hotels_attributes_providers.store');
        Route::get('edit/{attribute_id}/{type}/{code}', 'HotelsAttributesProvidersController@edit')->name('admin.hotels_attributes_providers.edit')->where('attribute_id', '[0-9]+');
        Route::put('update', 'HotelsAttributesProvidersController@update')->name('admin.hotels_attributes_providers.update');
        Route::delete('destroy/{id}', 'HotelsAttributesProvidersController@destroy')->name('admin.hotels_attributes_providers.destroy')->where('id', '[0-9]+');
    });

    // регионы
    Route::group(['prefix' => 'regions'], function () {
        Route::get('/{id?}', 'HotelsRegionsController@list')->name('admin.hotels_regions.list')->where('id', '[0-9]+');
        Route::get('create', 'HotelsRegionsController@create')->name('admin.hotels_regions.create');
        Route::post('store', 'HotelsRegionsController@store')->name('admin.hotels_regions.store');
        Route::get('edit/{id}', 'HotelsRegionsController@edit')->name('admin.hotels_regions.edit')->where('id', '[0-9]+');
        Route::put('update', 'HotelsRegionsController@update')->name('admin.hotels_regions.update');
        Route::delete('destroy/{id}', 'HotelsRegionsController@destroy')->name('admin.hotels_regions.destroy')->where('id', '[0-9]+');
    });

    // заказы
    Route::group(['prefix' => 'orders'], function () {
        Route::get('/', 'HotelsOrdersController@list')->name('admin.orders_hotels.list');
        Route::get('/offers/{id}', 'HotelsOrdersController@offers')->name('admin.orders_hotels.offers');
        Route::get('/guests/{id}', 'HotelsOrdersController@guests')->name('admin.orders_hotels.guests')->where('id', '[0-9]+');
    });
});

// пользователи портала
Route::group(['prefix' => 'portal-users'], function () {
    Route::get('/', 'PortalUsersController@list')->name('admin.portalusers.list');
    Route::get('create', 'PortalUsersController@create')->name('admin.portalusers.create');
    Route::post('store', 'PortalUsersController@store')->name('admin.portalusers.store');
    Route::get('edit/{id}', 'PortalUsersController@edit')->name('admin.portalusers.edit')->where('id', '[0-9]+');
    Route::put('update', 'PortalUsersController@update')->name('admin.portalusers.update');
    Route::delete('destroy/{id}', 'PortalUsersController@destroy')->name('admin.portalusers.destroy')->where('id', '[0-9]+');
});

// роли
Route::group(['prefix' => 'portal-users-role'], function () {
    Route::get('/', 'PortalUsersRoleController@list')->name('admin.portal_users_role.list');
    Route::get('create', 'PortalUsersRoleController@create')->name('admin.portal_users_role.create');
    Route::post('store', 'PortalUsersRoleController@store')->name('admin.portal_users_role.store');
    Route::get('edit/{id}', 'PortalUsersRoleController@edit')->name('admin.portal_users_role.edit')->where('id', '[0-9]+');
    Route::put('update', 'PortalUsersRoleController@update')->name('admin.portal_users_role.update');
    Route::delete('destroy/{id}', 'PortalUsersRoleController@destroy')->name('admin.portal_users_role.destroy')->where('id', '[0-9]+');
});

// пользователи админки
Route::group(['prefix' => 'users'], function () {
    Route::get('/', 'UsersController@list')->name('admin.users.list');
    Route::get('create', 'UsersController@create')->name('admin.users.create');
    Route::post('store', 'UsersController@store')->name('admin.users.store');
    Route::get('edit/{id}', 'UsersController@edit')->name('admin.users.edit')->where('id', '[0-9]+');
    Route::put('update', 'UsersController@update')->name('admin.users.update');
    Route::delete('destroy/{id}', 'UsersController@destroy')->name('admin.users.destroy')->where('id', '[0-9]+');
    Route::get('change-user-password/{id}', 'UsersController@changeUserPassword')->name('admin.users.changeuserpassword');
});

// роли
Route::group(['prefix' => 'role'], function () {
    Route::get('/', 'RoleController@list')->name('admin.role.list');
    Route::get('create', 'RoleController@create')->name('admin.role.create');
    Route::post('store', 'RoleController@store')->name('admin.role.store');
    Route::get('edit/{id}', 'RoleController@edit')->name('admin.role.edit')->where('id', '[0-9]+');
    Route::put('update', 'RoleController@update')->name('admin.role.update');
    Route::delete('destroy/{id}', 'RoleController@destroy')->name('admin.role.destroy')->where('id', '[0-9]+');
});

// логи
Route::group(['prefix' => 'logs'], function () {
    Route::get('/', 'SessionLogController@list')->name('admin.logs.list');
    Route::get('info/{id}', 'SessionLogController@info')->name('admin.logs.info')->where('id', '[0-9]+');
});

// история платежей
Route::group(['prefix' => 'orders-payment'], function () {
    Route::get('info/{id}', 'IndexController@ordersPaymentInfo')->name('admin.orders_payment.info')->where('id', '[0-9]+');
});

// лог заказов
Route::group(['prefix' => 'orders-log'], function () {
    Route::get('/{orderId}', 'OrdersLogController@list')->name('admin.orders_log.list')->where('orderId', '[0-9]+');
    Route::get('info/{id}', 'OrdersLogController@info')->name('admin.orders_log.info')->where('id', '[0-9]+');
});

// Комментарии к заказу
Route::group(['prefix' => 'order-messages'], function () {
    Route::get('/', 'OrderMessagesController@list')->name('admin.order_messages.list');
    Route::get('messages/{order_id}/{receiver_id}', 'OrderMessagesController@messages')->name('admin.order_messages.messages')->where('order_id', '[0-9]+')->where('receiver_id', '[0-9]+');
    Route::post('add-answer', 'OrderMessagesController@addAnswer')->name('admin.order_messages.add_answer');
    Route::delete('destroy/{id}', 'OrderMessagesController@destroy')->name('admin.order_messages.destroy')->where('id', '[0-9]+');
});

// настройки
Route::group(['prefix' => 'settings'], function () {
    Route::get('/', 'AppSettingsController@listSettings')->name('admin.settings.list');
    Route::get('create', 'AppSettingsController@create')->name('admin.settings.create');
    Route::post('store', 'AppSettingsController@store')->name('admin.settings.store');
    Route::get('edit/{id}', 'AppSettingsController@edit')->name('admin.settings.edit')->where('id', '[0-9]+');
    Route::put('update', 'AppSettingsController@update')->name('admin.settings.update');
    Route::delete('destroy/{id}', 'AppSettingsController@destroy')->name('admin.settings.destroy')->where('id', '[0-9]+');
});

//языки
Route::group(['prefix' => 'languages'], function () {
    Route::get('/', 'LanguagesController@list')->name('admin.languages.list');
    Route::get('create', 'LanguagesController@create')->name('admin.languages.create');
    Route::post('store', 'LanguagesController@store')->name('admin.languages.store');
    Route::get('edit/{id}', 'LanguagesController@edit')->name('admin.languages.edit')->where('id', '[0-9]+');
    Route::put('update', 'LanguagesController@update')->name('admin.languages.update');
    Route::delete('destroy/{id}', 'LanguagesController@destroy')->name('admin.languages.destroy')->where('id', '[0-9]+');
});

// Менеджер переводов
Route::group(['prefix' => 'tmanager'], function () {
    Route::get('/', 'TManagerController@list')->name('admin.tmanager.list');
    Route::get('create', 'TManagerController@create')->name('admin.tmanager.create');
    Route::post('store', 'TManagerController@store')->name('admin.tmanager.store');
    Route::get('edit/{id}', 'TManagerController@edit')->name('admin.tmanager.edit')->where('id', '[0-9]+');
    Route::put('update', 'TManagerController@update')->name('admin.tmanager.update');
    Route::delete('destroy/{id}', 'TManagerController@destroy')->name('admin.tmanager.destroy')->where('id', '[0-9]+');
});

Route::group(['prefix' => 'datatable'], function () {
    Route::any('sessionlog', 'DataTableController@getSessionLog')->name('admin.datatable.sessionlog');
    Route::any('admin-users', 'DataTableController@getAdminUsers')->name('admin.datatable.adminusers');
    Route::any('role', 'DataTableController@getRole')->name('admin.datatable.role');
    Route::any('settings', 'DataTableController@getSettings')->name('admin.datatable.settings');
    Route::any('portalusers', 'DataTableController@getPortalUsers')->name('admin.datatable.portalusers');
    Route::any('orders-railway', 'DataTableController@getOrdersRailways')->name('admin.datatable.ordersrailway');
    Route::any('trains-car', 'DataTableController@getTrainsCar')->name('admin.datatable.trainscar');
    Route::any('trains', 'DataTableController@getTrains')->name('admin.datatable.trains');
    Route::any('pages', 'DataTableController@getPages')->name('admin.datatable.pages');
    Route::any('tmanager', 'DataTableController@getTmanager')->name('admin.datatable.tmanager');
    Route::any('languages', 'DataTableController@getLanguages')->name('admin.datatable.languages');
    Route::any('orders', 'DataTableController@getOrders')->name('admin.datatable.orders');
    Route::any('ajax', 'DataTableController@action')->name('admin.datatable.ajax');
    Route::any('offices', 'DataTableController@getOffices')->name('admin.datatable.offices');
    Route::any('order_messages', 'DataTableController@getOrderMessages')->name('admin.datatable.order_messages');
    Route::any('messages/{receiver_id}', 'DataTableController@getMessages')->name('admin.datatable.messages')->where('receiver_id', '[0-9]+');
    Route::any('portal-users-role', 'DataTableController@getPortalUsersRole')->name('admin.datatable.portal_users_role');
    Route::any('railway-stations', 'DataTableController@getRailwayStation')->name('admin.datatable.stations');
    Route::any('hotel', 'DataTableController@getHotel')->name('admin.datatable.hotel');
    Route::any('hotels_attributes', 'DataTableController@getHotelsAttributes')->name('admin.datatable.hotels_attributes');
    Route::any('hotels_attributes_providers/{attribute_id}/provider/type', 'DataTableController@getHotelsAttributesProviders')->name('admin.datatable.hotels_attributes_providers')->where('attribute_id', '[0-9]+');
    Route::any('hotels_regions/{parent_id?}', 'DataTableController@getHotelsRegions')->name('admin.datatable.hotels_regions')->where('parent_id', '[0-9]+');
    Route::any('orders_hotel', 'DataTableController@getOrdersHotel')->name('admin.datatable.orders_hotel');
    Route::any('orders_log/{orderId}', 'DataTableController@getOrdersLog')->name('admin.datatable.orders_log')->where('orderId', '[0-9]+');

});
