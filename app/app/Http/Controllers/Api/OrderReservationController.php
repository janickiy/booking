<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelpers;
use App\Jobs\Soap1CRailwayOrderRefund;
use App\Services\External\RSB\RsbPaymentAPI;
use App\Services\QueueBalanced;
use Dompdf\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\{OrdersRailway, Orders, OrdersPayment};
use App\Helpers\StringHelpers;
use App\Services\External\InnovateMobility\Models\OrderFullCustomerRequest;
use App\Services\External\InnovateMobility\v1\OrderReservation;
use App\Services\External\InnovateMobility\Models\{RailwayReservationRequest,
    RailwayReturnAmountRequest,
    RailwayAutoReturnRequest,
    RailwayCreateExchangeRequest};
use App\Services\External\Payture\PaytureAPI;
use App\Services\Settings;
use App\Services\SessionLog;
use App\Helpers\NotificationHelpers;


/**
 * Class OrderReservationController
 * [Методы для работы с заказом РЖД(бронирование/возврат/обмен)]
 * @group Railway Order Reservation
 * @package App\Http\Controllers\Api
 */
class OrderReservationController extends AbstractOrderReservationController
{
    /**
     * OrderReservationController constructor.
     */
    public function __construct()
    {
        $this->setModel(new OrdersRailway());
        parent::__construct();
    }

    /**
     * Create
     * [Выдаём данные для создания бронирования]
     * @param Request $request
     * https://test.onelya.ru/ApiDocs/Api?apiId=Order-V1-Reservation-Create
     * @bodyParam ContactPhone string Контактный телефон
     * @bodyParam ContactEmails array Контактные емэйлы
     * @bodyParam Customers array required Пользователи услуг заказа
     * @bodyParam ReservationItems array required Услуги на бронирование
     * @response {
     * "id":1,
     * "complexOrderId":1,
     * "Amount":59999,
     * "imAmmount":40000,
     * "tax":{"rzhd":400,"agent":50,"total":450},
     * "ConfirmTill":"2019-03-03T00:00:00",
     * "Customers":1,
     * "ReservationResults":[],
     * }
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getCreate(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
               // 'ContactEmails' => 'required|array',
               // 'ContactEmails.*' => 'email',
                'Customers' => 'required|array',
                'ReservationItems' => 'required|array',
            ],
            [
              //  'ContactEmails.required' => 'Не указаны контактные емэйлы!',
                'Customers.required' => 'Не указаны пользователи услуг заказа!',
                'ReservationItems.required' => 'Не указаны услуги на бронирование!',
            ]
        );

        if ($validator->fails()) {
            return ResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);
        }

        $user = \Auth::user('web');
        $userId = isset($user->userId) ? $user->userId : 0;


        $orderPassengerData = [];

        if (isset($request->ReservationItems[0]['Passengers'])) {
            foreach ($request->ReservationItems[0]['Passengers'] as $passenger) {
                $orderPassengerData[$passenger['OrderCustomerIndex']] = $passenger;
            }
        }

        foreach ($request->input('Customers') as $customerItem) {
            $orderFullCustomerRequest = new OrderFullCustomerRequest($customerItem);

            if (!$orderFullCustomerRequest->validate()) {

                return ResponseHelpers::jsonResponse([
                    'error' => [$orderFullCustomerRequest->getValidationErrors()]
                ], 400);

            } else {
                $customers[] = $orderFullCustomerRequest->getBody();
            }
        }

        $reservations = [];

        foreach ($request->input('ReservationItems') as $reservationItem) {
            $reservation = new RailwayReservationRequest($reservationItem);

            if (!$reservation->validate()) {
                return ResponseHelpers::jsonResponse([
                    'error' => [$reservation->getValidationErrors()],
                    'code' => 400
                ], 400);
            } else {
                $reservations[] = $reservation->getBody();
            }
        }

        $email = $userId == 0 ? $request->ReservationItems[0]['Passengers'][0]["ContactEmail"] : $user->email;
        $settings = new Settings();
        $totalTax = (float)$settings->get('taxRailwayImRzdPurchase') + (float)$settings->get('taxRailwayImTrivagoPurchase');

        $response = OrderReservation::getCreate([
            'ContactPhone' => $request->input('ContactPhone'),
            'ContactEmails' => $request->input('ContactEmails'),
            'Customers' => $customers,
            'ReservationItems' => $reservations
        ], true, ['totalTax' => $totalTax]);

        if (!$response) {
            return [
                'error' => OrderReservation::getLastError(),
                'code' => 500
            ];
        }

        $result = StringHelpers::ObjectToArray($response);
        $blanks = [];

        if (isset($result["ReservationResults"]) && is_array($result["ReservationResults"])) {
            foreach ($result["ReservationResults"] as $reservationResult) {
                foreach ($reservationResult["Blanks"] as $blank) {
                    $blanks[] = $blank;
                }
            }
        }

        $passengers = [];

        if (isset($result["ReservationResults"]) && is_array($result["ReservationResults"])) {
            foreach ($result["ReservationResults"] as $reservationResult) {
                foreach ($reservationResult["Passengers"] as $passenger) {
                    if (isset($passenger['OrderCustomerReferenceIndex']) && isset($orderPassengerData[$passenger['OrderCustomerReferenceIndex']]['ContactEmail'])) {
                        $passenger = array_merge($passenger,
                            ['ContactEmail' => $orderPassengerData[$passenger['OrderCustomerReferenceIndex']]['ContactEmail']]);
                    }

                    $passengers[] = $passenger;
                }
            }
        }

        $response = StringHelpers::ObjectToArray($response);

        // $data_o['orderItems'] = 0;
        $data_o['userId'] = $userId;

        $complexOrderId = Orders::create($data_o)->id;

        $data_o_r['provider'] = 'IM';
        $data_o_r['userId'] = $userId;
        $data_o_r['Amount'] = $response['totalAmount'];
        $data_o_r['passengersData'] = $passengers;
        $data_o_r['orderStatus'] = OrdersRailway::ORDER_STATUS_CREATED;
        $data_o_r['orderData'] = [
            'orderId' => $result['OrderId'],
            // 'ConfirmTill' => $result['ConfirmTill'],
            'ContactPhone' => isset($request->input('ContactPhone')[0]) ? $request->input('ContactPhone')[0] : null,
            'ContactEmails' => isset($request->input('ContactEmails')[0]) ? $request->input('ContactEmails')[0] : null,
            'Customers' => $result['Customers'],
            'result' => $result
        ];

        $data_o_r['orderDocuments'] = $blanks;
        $data_o_r['complexOrderId'] = $complexOrderId;
        $data_o_r['payTill'] = str_replace('T', '', $result['ConfirmTill']);

        $id_orders_railway = OrdersRailway::create($data_o_r)->orderId;
        $orderItems[] = ['id' => $id_orders_railway, 'type' => Orders::ORDER_TYPE_RAILWAY];

        Orders::where('id', $complexOrderId)->update([
            'orderItems' => json_encode($orderItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        ]);

        SessionLog::orderLog($complexOrderId, 'create', 'Создания бронирования ЖД', false);


        NotificationHelpers::CreateOrder($id_orders_railway,'railway',$email);

        return ResponseHelpers::jsonResponse(
            [
                'id' => $id_orders_railway,
                'complexOrderId' => $complexOrderId,
                'Amount' => $response['totalAmount'],
                'imAmmount' => $response['imAmmount'],
                'tax' => ["rzhd" => (float)$settings->get('taxRailwayImRzdPurchase'), "agent" => (float)$settings->get('taxRailwayImTrivagoPurchase'), "total" => $totalTax],
                'ConfirmTill' => $response['ConfirmTill'],
                'Customers' => $response['Customers'],
                'ReservationResults' => $response['ReservationResults'],
            ]
        );
    }

    /**
     * Prolong Reservation
     * [Продление бронирования]
     * https://test.onelya.ru/ApiDocs/Api?apiId=Order-V1-Reservation-ProlongReservation
     *
     * @bodyParam OrderId integer required  Идентификатор заказа
     * @bodyParam OrderItemIds array Идентификаторы позиций в заказе на продление
     * @bodyParam ProlongReservationType Тип продления бронирования
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getProlongReservation(Request $request)
    {
        $messages = [
            'OrderId.required' => 'Не указан идентификатор заказа',
            'OrderId.numeric' => 'Неверно указан идентификатор заказа',
            'OrderItemIds.array' => 'Список идентификатора позиций в заказе на продление не соответсвует формату'
        ];

        $validator = Validator::make($request->all(), [
            'OrderId' => 'required|numeric',
            'OrderItemIds' => 'array|nullable',
        ], $messages);

        if ($validator->fails()) {
            return ResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);
        }

        $order = OrdersRailway::where('orderId', $request->OrderId)->first();

        if (!$order) {

            SessionLog::orderLog($request->OrderId, 'prolong-reservation', 'ЖД заявка не найдена!', true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse([
                'error' => 'ЖД заявка не найдена!'
            ], 404);
        }

        if ($order->orderStatus == 2) {

            SessionLog::orderLog($request->OrderId, 'prolong-reservation', 'ЖД заявка уже оплачена', true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse([
                'error' => 'ЖД заявка уже оплачена'
            ], 400);
        }

        if ($order->orderStatus == -1) {

            SessionLog::orderLog($request->OrderId, 'prolong-reservation', 'ЖД заявка уже оплачена', true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse([
                'error' => 'ЖД заявка отменена'
            ], 406);
        }

        $orderData = StringHelpers::ObjectToArray($order->orderData);

        $response = OrderReservation::doProlongReservation(
            [
                'OrderId' => $orderData['orderId'],
                "OrderItemIds" => $request->OrderItemIds,
                "ProlongReservationType" => $request->ProlongReservationType
            ]
        );

        if (!$response) {

            SessionLog::orderLog($request->OrderId, 'prolong-reservation', OrderReservation::getLastError()->Message, true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse([
                'error' => OrderReservation::getLastError()
            ], 500);
        }

        $result = StringHelpers::ObjectToArray($response);
        $result['ConfirmTill'] = str_replace('T', '', $result['ConfirmTill']);
        $order->update(['payTill' => $result['ConfirmTill'], 'orderStatus' => OrdersRailway::ORDER_STATUS_RESERVED]);

        SessionLog::orderLog($request->OrderId, 'prolong-reservation', 'Продление бронирования ЖД', false);

        return ResponseHelpers::jsonResponse(['ConfirmTill' => $result['ConfirmTill'], 'result' => true]);

    }

    /**
     * Return Amount
     * [Получение суммы планируемого автоматического возврата]
     * https://test.onelya.ru/ApiDocs/Api?apiId=Order-V1-Reservation-ReturnAmount
     * @queryParam OrderId integer required Идентификатор ЖД заказа
     * @queryParam OrderItemBlankIds array Идентификаторы бланков по которым необходимо получить суммы планируемого возврата. ( Можно указать один или все идентификаторы бланков)
     * @param Request $request array
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getReturnAmount(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'OrderId' => 'required|numeric',
                'OrderItemBlankIds' => 'array|nullable',
            ],
            [
                'OrderId.required' => 'Не указан идентификатор заказа',
                'OrderId.numeric' => 'Неверно указан идентификатор заказа',
                'OrderItemBlankIds.array' => 'Список пользователей заказа указан неверно',
            ]
        );

        if ($validator->fails()) {
            return ResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);
        }

        $user = $request->user('web');

        if (!$user) {

            SessionLog::orderLog($request->OrderId, 'return_amount', 'Пользователь не авторизован', true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse(['error' => true, 'message' => 'Auth required'], 401);
        }

        $order = $user->orders()->getById($request->OrderId)->first();

        if (!$order) {

            SessionLog::orderLog($request->OrderId, 'return_amount', 'Заказ не найден', true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse(['order' => null], 404);
        }

        if ($order->id != $request->OrderId) {
            $items = $order->getItemById($request->OrderId);

            if (!$items) {
                SessionLog::orderLog($request->OrderId, 'return_amount', 'Нет информации по заказу', true, StringHelpers::ObjectToArray($request));
                return ResponseHelpers::jsonResponse(['order' => null], 404);
            }
            $orderData = StringHelpers::ObjectToArray($items->orderData);

        } else {
            $items = $order->getItems();
            $orderData = StringHelpers::ObjectToArray($items[0]->orderData);
        }

        $customers = [];

        foreach ($orderData['Customers'] as $customer) {
            $customers[] = $customer;
        }

        $service = new RailwayReturnAmountRequest([
            'OrderItemBlankIds' => $request->OrderItemBlankIds ? $request->OrderItemBlankIds : null,
            'CheckDocumentNumber' => $customers[0]['DocumentNumber'],
            'ReturnTarget' => 'Return',
            'OrderItemId' => $orderData['result']['ReservationResults'][0]["OrderItemId"],
        ]);

        $response = OrderReservation::doReturnAmount([
            'ServiceReturnAmountRequest' => $service->getBody()
        ]);

        if (!$response) {

            SessionLog::orderLog($request->OrderId, 'return_amount', OrderReservation::getLastError()->Message, true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse([
                'error' => OrderReservation::getLastError()
            ], 500);
        }

        SessionLog::orderLog($request->OrderId, 'return_amount', 'Получение суммы планируемого автоматического возврата', false);

        return ResponseHelpers::jsonResponse($response);

    }

    /**
     * Auto Return
     * [Проведение автоматического возврата]
     * https://test.onelya.ru/ApiDocs/Api?apiId=Order-V1-Reservation-AutoReturn
     *
     * @bodyParam ServiceAutoReturnRequest sting required Входные данные на возврат услуги по отдельной позиции заказа
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getAutoReturn(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'OrderId' => 'required|numeric',
                'OrderItemBlankIds' => 'array|nullable',
            ],
            [
                'OrderId.required' => 'Не указан идентификатор заказа',
                'OrderId.numeric' => 'Неверно указан идентификатор заказа',
                'OrderItemBlankIds.array' => 'Список пользователей заказа указан неверно',
            ]
        );

        if ($validator->fails()) {
            return ResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);
        }
        $user = $request->user('web');

        if (!$user) {

            SessionLog::orderLog($request->OrderId, 'autoreturn', 'Пользователь не авторизован', true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse(['error' => true, 'message' => 'Auth required'], 401);
        }

        $order = $user->orders()->getById($request->OrderId)->first();

        if (!$order) {

            SessionLog::orderLog($request->OrderId, 'autoreturn', 'Заказ не найден', true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse(['order' => null], 404);
        }

        if ($order->id != $request->OrderId) {
            $items = $order->getItemById($request->OrderId);
            if (!$items) {

                SessionLog::orderLog($request->OrderId,'autoreturn', 'Нет информации по заказу', true, StringHelpers::ObjectToArray($request));

                return ResponseHelpers::jsonResponse(['order' => null], 404);
            }
            $orderData = StringHelpers::ObjectToArray($items->orderData);
            $orderId = $request->OrderId;
        } else {
            $items = $order->getItems();
            $orderData = StringHelpers::ObjectToArray($items[0]->orderData);
            $orderId = $order->getItems()[0]->orderId;
        }

        $customers = [];

        foreach ($orderData['Customers'] as $customer) {
            $customers[] = $customer;
        }

        $service = new RailwayAutoReturnRequest([
            'OrderItemBlankIds' => $request->OrderItemBlankIds ? $request->OrderItemBlankIds : null,
            'CheckDocumentNumber' => $customers[0]['DocumentNumber'],
            'ReturnTarget' => 'Return',
            'OrderItemId' => $orderData['result']['ReservationResults'][0]["OrderItemId"],
            'AgentReferenceId' => null,
        ]);

        if (!$service->validate()) {

            SessionLog::orderLog($request->OrderId, 'autoreturn', 'Ошибка ввалидации данных', true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse([
                'error' => [$service->getValidationErrors()]
            ], 400);
        }

        $response = OrderReservation::doAutoReturn(['ServiceAutoReturnRequest' => $service->getBody()]);

        if (!$response) {

            SessionLog::orderLog($request->OrderId, 'autoreturn', OrderReservation::getLastError()->Message, true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse([
                'error' => OrderReservation::getLastError()
            ], 500);
        }


        $amount = isset($response->ServiceReturnResponse->Amount) ? (int)$response->ServiceReturnResponse->Amount * 100 : 0.00;

        $orderPayment = OrdersPayment::getById($orderId)->where('status', 1)->first();

        if ($orderPayment) {
            switch ($orderPayment->provider) {
                case 'payture':

                    $responsePay = PaytureAPI::Refund($orderPayment->transactionId, $amount, $request->Cheque);
                    $responsePay = StringHelpers::ObjectToArray($responsePay);

                    if (isset($responsePay['error'])) {

                        SessionLog::orderLog($request->OrderId, 'autoreturn', $responsePay['error'], true, StringHelpers::ObjectToArray($request));

                        return ResponseHelpers::jsonResponse([
                            'error' => $responsePay['error']
                        ], $responsePay['code']);
                    }

                    $refund = OrdersPayment::create([
                        'payItems' => $orderPayment->payItems,
                        'complexOrderId' => $orderPayment->complexOrderId,
                        'amount' => (double) $amount / 100,
                        'provider' => $orderPayment->provider,
                        'type' => 'refund',
                        'request' => ['orderId' => $orderId, 'amount' => $amount, 'Cheque' => $request->Cheque],
                        'response' => $responsePay,
                        'userId' => $user->userId,
                        'clientId' => $user->clientId,
                        'holdingId' => $user->holdingId
                    ]);

                    $refund->status = $refund::STATUS_FINISHED;
                    $refund->transactionId = $orderPayment->transactionId;
                    $refund->save();

                    break;

                case 'rsb':
                    $transId = $orderPayment->transactionId;
                    $responsePay = RsbPaymentAPI::Refund($transId, $amount);

                    if (isset($responsePay['error'])) {

                        SessionLog::orderLog($request->OrderId, 'autoreturn', $responsePay['error'], true, StringHelpers::ObjectToArray($request));

                        return ResponseHelpers::jsonResponse([
                            'error' => $responsePay['error']
                        ], $responsePay['code']);
                    }

                    $refund = OrdersPayment::create([
                        'payItems' => $orderPayment->payItems,
                        'complexOrderId' => $orderPayment->complexOrderId,
                        'amount' => (double) $amount / 100,
                        'provider' => $orderPayment->provider,
                        'type' => 'refund',
                        'request' => ['orderId' => $orderId, 'amount' => $amount, 'Cheque' => $request->Cheque],
                        'response' => $responsePay,
                        'userId' => $user->userId,
                        'clientId' => $user->clientId,
                        'holdingId' => $user->holdingId
                    ]);

                    $refund->status = $refund::STATUS_FINISHED;
                    $refund->transactionId = $orderPayment->transactionId;
                    $refund->save();

            }
        }

        QueueBalanced::balance(new Soap1CRailwayOrderRefund($orderId, $request->get('OrderItemBlankIds', null)), 'one-c');

        $result = StringHelpers::ObjectToArray($response);

        $blanks = [];

        if (isset($result["ReservationResults"]) && is_array($result["ReservationResults"])) {
            foreach ($result["ReservationResults"] as $reservationResult) {
                foreach ($reservationResult["Blanks"] as $blank) {
                    $blanks[] = $blank;
                }
            }
        }

        OrdersRailway::where('orderId', $orderId)->update(['orderDocuments' => json_encode($blanks, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);

        SessionLog::orderLog($request->OrderId, 'autoreturn', 'Проведение автоматического возврата', false);

        return ResponseHelpers::jsonResponse($response);

    }

    /**
     * Add Upsale
     * [Добавление апсейла (доп. сервиса) к основной услуге]
     * https://test.onelya.ru/ApiDocs/Api?apiId=Order-V1-Reservation-AddUpsale
     *
     * @bodyParam OrderId integer Идентификатор заказа
     * @bodyParam OrderItemId integer Идентификатор позиции заказа основной услуги
     * @bodyParam ServiceAddUpsaleRequest string required Запрос на добавление апсейла Принимает типы
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getAddUpsale(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'OrderId' => 'numeric|nullable',
                'OrderItemId' => 'numeric|nullable',
                'ServiceAddUpsaleRequest' => 'array|required',
            ],
            [
                'OrderId.numeric' => 'Неверно указан идентификатор заказа',
                'OrderItemId.numeric' => 'Неверно указан дентификатор позиции заказа основной услуги',
                'ServiceAddUpsaleRequest.required' => 'Не указан запрос на добавление апсейла',
                'ServiceAddUpsaleRequest.array' => 'Неверно указан запрос на добавление апсейла',
            ]
        );

        if ($validator->fails()) {
            return ResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);
        }

        $order = OrdersRailway::where('orderId', $request->OrderId)->first();

        if (!$order) {

            SessionLog::orderLog($request->OrderId, 'addupsale', 'ЖД заявка не найдена!', true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse([
                'error' => 'ЖД заявка не найдена!'
            ], 404);
        }

        $orderData = StringHelpers::ObjectToArray($order->orderData);

        $response = OrderReservation::doAddUpsale(
            [
                'OrderId' => $orderData['orderId'],
                'OrderItemId' => $request->OrderItemId,
                'ServiceAddUpsaleRequest' => $request->ServiceAddUpsaleRequest,
            ]
        );

        if (!$response) {

            SessionLog::orderLog($request->OrderId, 'addupsale', OrderReservation::getLastError()->Message, true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse([
                'error' => OrderReservation::getLastError()
            ], 500);
        }

        SessionLog::orderLog($request->OrderId,'addupsale', 'Добавление апсейла (доп. сервиса) к основной услуге', false);

        return ResponseHelpers::jsonResponse($response);
    }

    /**
     * Refuse Upsale
     * [Отказ от апсейла (доп. сервиса)]
     * https://test.onelya.ru/ApiDocs/Api?apiId=Order-V1-Reservation-RefuseUpsale
     *
     * @bodyParam OrderId integer required Идентификатор заказа
     * @bodyParam OrderItemId integer required Идентификатор позиции заказа основной услуги
     * @bodyParam OrderCustomerIds array Список пользователей заказа
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getRefuseUpsale(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'OrderId' => 'required|numeric',
                'OrderItemId' => 'required|numeric',
                'OrderCustomerIds' => 'required|array',
            ],
            [
                'OrderId.required' => 'Не указан идентификатор заказа',
                'OrderId.numeric' => 'Неверно указан идентификатор заказа',
                'OrderItemId.required' => 'Не указан идентификатор позиции заказа основной услуги',
                'OrderItemId.numeric' => 'Неверно указан идентификатор позиции заказа основной услуги',
                'OrderCustomerIds.required' => 'Не указан идентификатор позиции заказа основной услуги',
                'OrderCustomerIds.array' => 'Список пользователей заказа указан неверно',
            ]
        );

        if ($validator->fails()) {
            return ResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);
        }

        $order = OrdersRailway::where('orderId', $request->OrderId)->first();

        if (!$order) {

            SessionLog::orderLog($request->OrderId, 'refuseupsale', 'Добавление апсейла (доп. сервиса) к основной услуге', true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse([
                'error' => 'ЖД заявка не найдена!'
            ], 404);
        }

        $orderData = StringHelpers::ObjectToArray($order->orderData);

        $response = OrderReservation::doRefuseUpsale(
            [
                'OrderId' => $orderData['orderId'],
                'OrderItemId' => $request->input('OrderItemId'),
                'OrderCustomerIds' => $request->input('OrderCustomerIds')
            ]
        );

        if (!$response) {

            SessionLog::orderLog($request->OrderId,'refuseupsale', OrderReservation::getLastError()->Message, true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse([
                'error' => OrderReservation::getLastError()
            ], 500);
        }

        SessionLog::orderLog($request->OrderId, 'refuseupsale', 'Отказ от апсейла', false);

        return ResponseHelpers::jsonResponse($response);
    }

    /**
     * Create Exchange
     * [Создание переоформления]
     * https://test.onelya.ru/ApiDocs/Api?apiId=Order-V1-Reservation-CreateExchange
     *
     * @bodyParam ServiceCreateExchangeRequest array required Услуги на бронирование
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getCreateExchange(Request $request)
    {

        $service = new RailwayCreateExchangeRequest($request->ServiceCreateExchangeRequest);

        if (!$service->validate()) {
            return ResponseHelpers::jsonResponse([
                'error' => [$service->getValidationErrors()]
            ], 400);
        }

        $response = OrderReservation::doCreateExchange(
            [
                "ServiceCreateExchangeRequest" => $service->getBody()
            ]
        );

        if (!$response) {
            return ResponseHelpers::jsonResponse([
                'error' => OrderReservation::getLastError()
            ], 500);
        }

        return ResponseHelpers::jsonResponse($response);
    }

    /**
     * Confirm Exchange
     * [Подтверждение переоформления]
     * https://test.onelya.ru/ApiDocs/Api?apiId=Order-V1-Reservation-ConfirmExchange
     *
     * @bodyParam OrderItemId integer required Идентификатор позиции для подтверждения
     * @bodyParam CheckDocumentNumber string required Номер документа для проверки
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getConfirmExchange(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'OrderItemId' => 'required|numeric',
                'CheckDocumentNumber' => 'required',
            ],
            [
                'OrderItemId.required' => 'Не указан идентификатор позиции для подтверждения',
                'OrderItemId.numeric' => 'Неверно указан идентификатор позиции для подтверждения',
                'CheckDocumentNumber.required' => 'Не указан номер документа для проверки',
            ]
        );

        if ($validator->fails()) {
            return ResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);
        }

        $response = OrderReservation::doConfirmExchange(
            [
                'OrderItemId' => $request->input('OrderItemId'),
                'CheckDocumentNumber' => $request->input('CheckDocumentNumber'),
            ]
        );

        if (!$response) {
            return ResponseHelpers::jsonResponse([
                'error' => OrderReservation::getLastError()
            ], 500);
        }

        return ResponseHelpers::jsonResponse($response);
    }

    /**
     * Validate customer
     * [Проверка пассажира на валидность]
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function validateCustomers(Request $request)
    {
        $errors = [];

        foreach ($request->Customers as $ind => $customerItem) {
            $customer = new OrderFullCustomerRequest($customerItem);

            if (!$customer->validate()) {
                $errors[$ind] = $customer->getValidationErrors();
            }

            if (isset($customerItem['DocumentType'])) {
                $validator = Validator::make($customerItem, ['DocumentNumber' => strtolower($customerItem['DocumentType'])], ['DocumentNumber.' . strtolower($customerItem['DocumentType']) => 'Неверно указан номер документа']);

                if ($validator->errors()->toArray()) $errors[$ind] = $validator->errors()->toArray();
            }
        }

        if (count($errors) > 0) {
            return ResponseHelpers::jsonResponse([
                'errors' => $errors
            ], 400);
        }

        return ResponseHelpers::jsonResponse([
            'valid' => true
        ], 200);
    }
}
