<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::group(['prefix' => 'v1'], function () {

    /*Route::get('test', [
        'as' => 'api.v1.test.test',
        'uses' => 'TestController@test'
    ]);*/

    /**
     * API маршрутизация работа с пользователями портала
     */
    Route::group(['prefix' => 'auth'], function () {

        Route::get('check', [
            'as' => 'api.v1.auth.check',
            'uses' => 'AuthController@check'
        ]);

        // авторизация
        Route::post('login', [
            'as' => 'api.v1.auth.login',
            'uses' => 'AuthController@login'
        ]);

        // выйти
        Route::get('logout', [
            'as' => 'api.v1.auth.logout',
            'uses' => 'AuthController@logout'
        ]);

        // восстановление пароля
        Route::post('password/reset', [
            'as' => 'api.v1.auth.password_reset',
            'uses' => 'AuthController@reset'
        ]);

        Route::post('password/email', [
            'as' => 'api.v1.auth.password_email',
            'uses' => 'AuthController@email'
        ]);

        // регистрация
        Route::put('registration', [
            'as' => 'api.v1.auth.registration',
            'uses' => 'AuthController@registration'
        ]);

        // верификация номера телефона
        Route::post('verify/mobile', [
            'as' => 'api.v1.auth.verify.mobile',
            'uses' => 'AuthController@verifyMobile'
        ]);

        // отправка кода подтверждения номера телефона
        Route::any('verify/sendotp', [
            'as' => 'api.v1.auth.verify.sendotp',
            'uses' => 'AuthController@sendOtp'
        ]);

        Route::post('verify/2factor', [
            'as' => 'api.v1.auth.verify.2factor',
            'uses' => 'AuthController@verifyTwoFactor'
        ]);

        Route::post('verify/2factorEmail', [
            'as' => 'api.v1.auth.verify.2factor_email',
            'uses' => 'AuthController@verifyTwoFactorEmail'
        ]);


        Route::get('get/{key}/{locale?}', [
            'as' => 'api.v1.auth.get',
            'uses' => 'AuthController@auth'
        ]);


        Route::get('domains', [
            'as' => 'api.v1.auth.domains',
            'uses' => 'AuthController@domains'
        ]);

        Route::get('info', [
            'as' => 'api.v1.auth.info',
            'uses' => 'AuthController@info'
        ]);
    });

    /*
        Route::get('news/{page?}', [
            'as' => 'api.v1.news.list',
            'uses' => 'NewsController@list'
        ]);
    */

    Route::get('jobs/progress/{id}', [
        'as' => 'api.v1.jobs.progress',
        'uses' => 'JobMonitorController@progress'
    ]);


    Route::post('reference/{model}', [
        'as' => 'api.v1.references.query',
        'uses' => 'ReferencesController@query'
    ]);

    Route::get('reference/static/{section?}', [
        'as' => 'api.v1.references.static',
        'uses' => 'ReferencesController@staticReferences'
    ]);

    /**
     * API маршрутизация Аэроэкспресс
     */
    Route::group(['prefix' => 'aeroexpress'], function () {
        Route::post('/search', ['as' => 'api.v1.aeroexpress.search', 'uses' => 'AeroexpressController@search']);
        Route::post('/info', ['as' => 'api.v1.aeroexpress.info', 'uses' => 'AeroexpressController@info']);
        Route::post('/create', ['as' => 'api.v1.aeroexpress.getcreate', 'uses' => 'AeroexpressOrderReservationController@getCreate']);
        Route::post('/auto-return', ['as' => 'api.v1.aeroexpress.autoreturn', 'uses' => 'AeroexpressOrderReservationController@getAutoReturn']);
        Route::post('/void', ['as' => 'api.v1.aeroexpress.void', 'uses' => 'AeroexpressOrderReservationController@getVoid']);
        Route::get('/tariff-and-routes', ['as' => 'api.v1.aeroexpress.gettariffandroutes', 'uses' => 'AeroexpressController@tariffAndRoutes']);
    });

    /**
     * API маршрутизация Автобусов
     */
    Route::group(['prefix' => 'bus'], function () {
        Route::post('/race-pricing', ['as' => 'api.v1.bus.racepricing', 'uses' => 'BusController@racePricing']);
        Route::post('/bus-route', ['as' => 'api.v1.bus.busroute', 'uses' => 'AeroexpressOrderReservationController@busRoute']);
        Route::post('/race-details', ['as' => 'api.v1.bus.racedetails', 'uses' => 'AeroexpressOrderReservationController@raceDetails']);
    });

    /**
     * API маршрутизация дополнительное питание (за отдельную плату)
     * API маршрутизация получения информации по заказам
     */
    Route::group(['prefix' => 'orders'], function () {

        // Получение информации о заказе
        Route::get('/{orderId}', ['as' => 'api.v1.orders.orderinfo', 'uses' => 'OrderInfoController@getOrderInfo'])->where('orderId', '[0-9]+');

        // Получение информации о заказах за период
        Route::post('/{page?}', ['as' => 'api.v1.orders.orderlist', 'uses' => 'OrderInfoController@getOrderList']);
    });

    Route::group(['prefix' => 'railway'], function () {

        Route::post('search', [
            'as' => 'api.v1.railway.search',
            'uses' => 'RailwayController@search'
        ]);

        Route::get('history', [
            'as' => 'api.v1.railway.history.get',
            'uses' => 'RailwayController@getHistory'
        ]);

        Route::delete('history', [
            'as' => 'api.railway.history.delete',
            'uses' => 'RailwayController@deleteHistory'
        ]);

        Route::post('train_schemes', [
            'as' => 'api.v1.railway.trainschemes',
            'uses' => 'RailwayController@trainSchemes'
        ]);

        Route::post('route', [
            'as' => 'api.v1.railway.route',
            'uses' => 'RailwayController@getRoute'
        ]);

        Route::post('cars', [
            'as' => 'api.v1.railway.cars',
            'uses' => 'RailwayController@getCarPricing'
        ]);

        /**
         * API маршрутизация дополнительное питание (за отдельную плату)
         */
        Route::group(['prefix' => 'additionalmeal'], function () {
            // Запрос информации по дополнительному питанию
            Route::post('/pricing', ['as' => 'api.v1.additionalmeal.pricing', 'uses' => 'AdditionalMealController@getPricing']);

            // Cоздание операции для дальнейшей покупки дополнительного питания для перевозки
            Route::post('/checkout', ['as' => 'api.v1.additionalmeal.checkout', 'uses' => 'AdditionalMealController@getCheckout']);

            // Отмена дополнительного питания
            Route::post('/cancel', ['as' => 'api.v1.additionalmeal.cancel', 'uses' => 'AdditionalMealController@getCancel']);

            // Покупка дополнительного питания для перевозки
            Route::post('/purchase', ['as' => 'api.v1.additionalmeal.purchase', 'uses' => 'AdditionalMealController@getPurchase']);

            // Возврат дополнительного питания
            Route::post('/return', ['as' => 'api.v1.additionalmeal.return', 'uses' => 'AdditionalMealController@getReturn']);
        });

        /**
         * API маршрутизация дополнительный багаж
         */
        Route::group(['prefix' => 'additionalbaggage'], function () {
            // Справка по стоимости перевозке багажа.
            Route::post('/pricing', ['as' => 'api.v1.additionalbaggage.pricing', 'uses' => 'AdditionalBaggageController@getPricing']);

            // Бронирование перевозки багажа
            Route::post('/book', ['as' => 'api.v1.additionalbaggage.book', 'uses' => 'AdditionalBaggageController@getBook']);

            // Отмена бронирования
            Route::post('/cancel', ['as' => 'api.v1.additionalbaggage.cancel', 'uses' => 'AdditionalBaggageController@getCancel']);

            // Подтверждение брони перевозки багажа
            Route::post('/confirm', ['as' => 'api.v1.additionalbaggage.confirm', 'uses' => 'AdditionalBaggageController@getConfirm']);

            // Отмена оплаченной перевозки багажа
            Route::post('/return', ['as' => 'api.v1.additionalbaggage.return', 'uses' => 'AdditionalBaggageController@getReturn']);

        });

        /**
         * API маршрутизация оформление ЖД карт
         */
        Route::group(['prefix' => 'card'], function () {
            // Запрос информации по вариантам и ценам доступных к оформлению ЖД карт
            Route::post('/pricing', ['as' => 'api.v1.card.pricing', 'uses' => 'CardController@getPricing']);

            // Проверка данных и создание операции для дальнейшей покупки ЖД карты
            Route::post('/checkout', ['as' => 'api.v1.card.checkout', 'uses' => 'CardController@getCheckout']);

            // Покупка ЖД карты
            Route::post('/purchase', ['as' => 'api.v1.card.purchase', 'uses' => 'CardController@getPurchase']);

            // Отмена создания операции для ЖД карты
            Route::post('/cancel', ['as' => 'api.v1.card.cancel', 'uses' => 'CardController@getCancel']);
        });

        /**
         * API маршрутизация методы по работе с ЖД-билетами в заказе и дополнительные методы по работе с ЖД-билетами в заказе
         */
        Route::group(['prefix' => 'reservation'], function () {

            //  Создание бронирования
            Route::post('/create',
                ['as' => 'api.v1.reservation.create', 'uses' => 'OrderReservationController@getCreate']);

            // Продление бронирования
            Route::post('/prolong-reservation', [
                'as' => 'api.v1.reservation.prolongreservation',
                'uses' => 'OrderReservationController@getProlongReservation'
            ]);

            /* // Получение маршрут-квитанции
             Route::post('/blank',
                 ['as' => 'api.v1.reservation.blank', 'uses' => 'OrderReservationController@getBlank']);

             // Отмена бронирования
             Route::post('/cancel',
                 ['as' => 'api.v1.reservation.cancel', 'uses' => 'OrderReservationController@getCancel']);*/

            // Получение суммы планируемого автоматического возврата
            Route::post('/return-amount',
                ['as' => 'api.v1.reservation.return_amount', 'uses' => 'OrderReservationController@getReturnAmount']);

            // Проведение автоматического возврата
            Route::post('/auto-return',
                ['as' => 'api.v1.reservation.autoreturn', 'uses' => 'OrderReservationController@getAutoReturn']);

            // Добавление апсейла (доп. сервиса) к основной услуге
            Route::post('/add-upsale',
                ['as' => 'api.v1.reservation.addupsale', 'uses' => 'OrderReservationController@getAddUpsale']);

            // Отказ от апсейла
            Route::post('/refuse-upsale',
                ['as' => 'api.v1.reservation.refuseupsale', 'uses' => 'OrderReservationController@getRefuseUpsale']);

            // Создание переоформления
            Route::post('/create-exchange', [
                'as' => 'api.v1.reservation.createexchange',
                'uses' => 'OrderReservationController@getCreateExchange'
            ]);

            // Подтверждение переоформления
            Route::post('/confirm-exchange', [
                'as' => 'api.v1.reservation.confirmexchange',
                'uses' => 'OrderReservationController@getConfirmExchange'
            ]);

            // Получение и обновление информации о бланках от поставщика
            Route::post('/update-blanks',
                ['as' => 'api.v1.reservation.updateblanks', 'uses' => 'ReservationController@getUpdateBlanks']);

            // Дополнительные методы по работе с ЖД-билетами в заказе
            Route::post('/electronic-registration', [
                'as' => 'api.v1.reservation.electronicregistration',
                'uses' => 'ReservationController@getElectronicRegistration'
            ]);

            // Дополнительные методы по работе с ЖД-билетами в заказе
            Route::post('/meal-option',
                ['as' => 'api.v1.reservation.mealoption', 'uses' => 'ReservationController@getMealOption']);

            //Получение маршрут-квитанции в формате HTML
            Route::post('/blank-as-html',
                ['as' => 'api.v1.reservation.blankashtml', 'uses' => 'ReservationController@getBlankAsHtml']);

            // Проверка возможности транзитного проезда
            Route::post('/check-transit-permission-approval', [
                'as' => 'api.v1.reservation.checktransitpermissionapproval',
                'uses' => 'ReservationController@getCheckTransitPermissionApproval'
            ]);

            // Проверка пассажиров
            Route::post('/passengers-validate', [
                'as' => 'api.v1.reservation.passengersValidate',
                'uses' => 'OrderReservationController@validateCustomers'
            ]);

        });
    });

    Route::group(['namespace' => 'Offices', 'prefix' => 'offices'], function () {
        Route::get('', 'OfficesController@index');
        Route::get('/office/{officeId}', 'OfficesController@getOffice');
        Route::get('/closest/{lat?}/{lon?}', 'OfficesController@getClosest');
    });

    Route::group(['prefix' => 'user'], function () {
        Route::get('info', 'UserController@getUser')->name('api.v1.user.get');
        Route::patch('contacts', 'UserController@updateUserContacts')->name('api.v1.user.updateContacts');
        Route::patch('password', 'UserController@changePassword')->name('api.v1.user.changePassword');
    });

    Route::group(['prefix' => 'passengers'], function () {
        Route::get('list/{page?}', ['as' => 'api.v1.passenger.list', 'uses' => 'PassengerController@list']);
        Route::post('store', ['as' => 'api.v1.passenger.store', 'uses' => 'PassengerController@store']);
        Route::get('passenger/{passengerId}', ['as' => 'api.v1.passenger.passenger', 'uses' => 'PassengerController@getPassenger'])->where('passengerId', '[0-9]+');
        Route::patch('update/{passengerId}', ['as' => 'api.v1.passenger.update', 'uses' => 'PassengerController@update'])->where('passengerId', '[0-9]+');
        Route::delete('delete/{passengerId}', ['as' => 'api.v1passenger.destroy', 'uses' => 'PassengerController@destroy'])->where('passengerId', '[0-9]+');
    });

    Route::group(['prefix' => 'frontend'], function () {
        // получение контента
        Route::get('page/{slug}', 'FrontendController@page')->name('api.v1.frontend.page');
        // меню
        Route::get('menu/{parent_id?}', 'FrontendController@menu')->name('api.v1.frontend.menu')->where('{parent_id', '[0-9]+');
        /*// Список заказов
        Route::get('orders', 'FrontendController@orders')->name('api.v1.frontend.orders');
        // информация по заказу
        Route::get('order-info/{id}', 'FrontendController@orderInfo')->name('api.v1.frontend.orders')->where('id', '[0-9]+');*/
        // отправить сообщение по заказу
        Route::put('message/send', 'FrontendController@messageSend')->name('api.v1.frontend.message_send');
        // все сообщения по заказу по всем его частям
        Route::get('message/get/{order_id}', 'FrontendController@getOrderMessages')->name('api.v1.frontend.get_order_messages')->where('order_id', '[0-9]+');
        // коментарий к конкретной части заказа
        Route::get('message/get/{order_id}/{order_item_id}', 'FrontendController@getOrderMessages')->name('api.v1.frontend.get_order_messages')->where('order_id', '[0-9]+')->where('order_item_id', '[0-9]+');
        // переключение языков
        Route::get('lang/{locale}', 'FrontendController@lang')->name('api.v1.frontend.lang');
        // получение бланков
        Route::get('get-pdf-blanks/{order_id}/{order_item_id?}', 'FrontendController@getPDFBlanks')->name('api.v1.frontend.get_pdf_blanks')->where('order_id', '[0-9]+');
        // получение справочников
        Route::get('references', ['as' => 'api.v1.frontend.references', 'uses' => 'FrontendController@getReferences']);

    });

    /**
     * API PAYMENTS
     */

    Route::group(['prefix' => 'payments'], function () {
        // Проведения клиентского платежа
        Route::post('pay', [
            'as' => 'api.v1.payments.pay',
            'uses' => 'PaymentsController@getPay'
        ]);

        // Проведения клиентского платежа по ссылке
        Route::any('quickpay', [
            'as' => 'api.v1.payments.quickpay',
            'uses' => 'PaymentsController@getQuickPay'
        ]);

        Route::any('secure/{system}/{transactionId?}', [
            'as' => 'api.v1.payments.3ds',
            'uses' => 'PaymentsController@finish3DS'
        ]);

        Route::post('/pay_links', [
            'as' => 'api.v1.payments.pay_links',
            'uses' => 'PaymentsController@getPayLinks'
        ]);
    });

    /**
     * API маршрутизация для avia
     */
    Route::group(['prefix' => 'avia'], function () {

        // Поиск предложений
        Route::post('/search', [
            'as' => 'api.v1.avia.search',
            'uses' => 'AviaController@search'
        ]);

        // Брендированные тарифы (получение тарифов по конкретному перелету в формате тарифов поиска)
        Route::post('/brands', [
            'as' => 'api.v1.avia.brands',
            'uses' => 'AviaController@brands'
        ]);

        // Прайсинг перелета (получение детализированных (базовая стоимость, таксы, сборы) тарифов по конкретному перелету)
        Route::post('/pricing', [
            'as' => 'api.v1.avia.pricing',
            'uses' => 'AviaController@pricing'
        ]);

        // Бронирование перелета
        Route::post('/booking', [
            'as' => 'api.v1.avia.booking',
            'uses' => 'AviaController@booking'
        ]);

        // Чтение бронирования
        Route::post('/retrieve', [
            'as' => 'api.v1.avia.retrieve',
            'uses' => 'AviaController@retrieve'
        ]);

        // Отмена бронирования
        Route::post('/cancel', [
            'as' => 'api.v1.avia.cancel',
            'uses' => 'AviaController@cancel'
        ]);

        // Возврат билетов
        Route::post('/refund', [
            'as' => 'api.v1.avia.refund',
            'uses' => 'AviaController@refund'
        ]);

        // Получение правил перелета
        Route::post('/getRules', [
            'as' => 'api.v1.avia.rules',
            'uses' => 'AviaController@getRules'
        ]);

        // Выписка билетов
        Route::post('/ticketing', [
            'as' => 'api.v1.avia.ticketing',
            'uses' => 'AviaController@ticketing'
        ]);

        Route::get('/rkworker', [
            'as' => 'api.v1.avia.rkworker',
            'uses' => 'AviaController@rkWorker'
        ]);
    });

    Route::group(['prefix' => 'onec'], function () {

        Route::group(['prefix' => 'push'], function () {
            // Быстрого проведения клиентского платежа одним действием
            Route::post('/client', [
                'as' => 'api.v1.onec.push.client',
                'uses' => 'OneCPushController@client'
            ]);
            Route::post('/account', [
                'as' => 'api.v1.onec.push.account',
                'uses' => 'OneCPushController@paymentAccount'
            ]);
        });
    });

});