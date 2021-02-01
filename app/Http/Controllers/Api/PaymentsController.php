<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelpers;
use App\Http\Controllers\Controller;
use App\Jobs\Soap1CRailwayOrderPush;
use App\Models\{
    OrdersAeroexpress, OrdersRailway, Orders, Passengers, User, OrdersPayment
};
use App\Services\QueueBalanced;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Helpers\{
    StringHelpers, NotificationHelpers, DateTimeHelpers
};
use App\Services\External\InnovateMobility\v1\OrderReservation;
use App\Helpers\LangHelper;
use App\Services\Settings;
use URL;
use App\Services\SessionLog;
use App\Services\Payments\DmsPayFacade;

/**
 * Class PayturePayments
 * [Методы работы с платежным шлюзом Payture API]
 * @group Payments orders
 * APIs for payments orders
 */
class PaymentsController extends Controller
{

    /**
     * Pay
     * [Оплата заказа]
     * @bodyParam OrderId integer required Уникальный идентификатор платежа в системе ТСП
     * @bodyParam PayInfo array Параметры для совершения транзакции
     * @bodyParam Customer array Контактые данные
     * @bodyParam Passengers array Данные пассажиров
     *
     * @response {
     * "OrderId": 605,
     * "Customers": [],
     * "items": []
     * }
     *
     * @response 202 {
     * "redirectURL": "http://url.to/redirect",
     * "method": "POST",
     * "formData": {
     *  "fieldName": "filedValue"
     * }
     * }
     *
     * @response 404 {
     * "orders": null
     * }
     *
     * @response 401 {
     * "error": true,
     * "message": "Auth required"
     * }
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getPay(Request $request)
    {


        // получем id авторизованого пользователя
        // если пользователь не авторизован, ищем id email или создаем учетную запись не авторизованного пользователя

        $authorized_user = true;

        $user = \Auth::user('web');
        $userId = isset($user->userId) ? $user->userId : 0;

        if ($userId == 0) {
            $authorized_user = false;
            $user = User::where('email', 'ilike', $request->Customer['email'])->first();

            if ($user) {
                $userId = $user->userId;
            }
        }


        $validator = Validator::make($request->all(),
            [
                'OrderId' => 'required|numeric',
                'PayInfo' => isset($user) && count($user->role) > 0 ? 'array|nullable' : 'required|array',
                'Customer.email' => 'required|email',
            ],
            [
                'PayInfo.required' => 'Не указаны параметры для совершения транзакции!',
                'OrderId.required' => 'Не указан идентификатор платежа в системе ТСП!',
                'OrderId.numeric' => 'Не верно указан идентификатор платежа в системе ТСП!',
            ]
        );

        if ($validator->fails()) {
            return ResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);
        }

        // получем id авторизованого пользователя
        // если пользователь не авторизован, ищем id email или создаем учетную запись не авторизованного пользователя

        $authorized_user = true;

        $user = \Auth::user('web');
        $userId = isset($user->userId) ? $user->userId : 0;

        if ($userId == 0) {
            $authorized_user = false;
            $user = User::where('email', 'ilike', $request->Customer['email'])->first();

            if ($user)
                $userId = $user->userId;
        }

        $orders = Orders::GetById($request->OrderId)->first();

        if (!$orders) {

            SessionLog::orderLog($request->OrderId, 'pay',  'Заказ с OrderId ' . $request->OrderId . ' не найден', true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse([
                'error' => LangHelper::trans('errors.order_not_found')
            ], 404);
        }

        $amount = 0.00;
        $items = [];
        $ordersIds = [];

        if ($orders->id == $request->OrderId) {
            // оплачиваем весь заказ

            $complexOrderId = $orders->id;

            foreach ($orders["orderItems"] as $order) {
                switch ($order->type) {
                    case 'railway':

                        $ordersData = $orders->getItemById($order->id);
                        $sum = $ordersData->Amount;
                        $amount += $sum;
                        $payItems[] = ['id' => $order->id, 'type' => $order->type];
                        $ordersIds[] = $order->id;
                        $type[$order->id] = $order->type;

                        if ($authorized_user === false) {
                            $userId = self::addUser($ordersData->passengersData, $request);
                            $ordersData->userId = $userId;
                            $ordersData->save();
                            $orders->userId = $userId;
                            $orders->save();
                        }

                        // добовляем пасажира
                        if (isset($request->Passengers)) {

                            foreach ($request->Passengers as $row) {

                                self::addPassenger($row, $userId);
                            }
                        }

                        $items[] = self::getItemFormat($ordersData, $order->type);

                        break;
                }
            }

        } else {
            switch ($orders["orderItems"][0]->type) {
                case 'railway':

                    $ordersRailway = OrdersRailway::where('orderId', $request->OrderId)->first();

                    if (!$ordersRailway) {

                        SessionLog::orderLog($request->OrderId, 'pay',  'ЖД заказ с OrderId ' . $request->OrderId . ' не найден', true, StringHelpers::ObjectToArray($request));

                        return ResponseHelpers::jsonResponse([
                            'error' => LangHelper::trans('errors.order_not_found')
                        ], 404);
                    }

                    $sum = $ordersRailway->Amount;
                    $amount += $sum;
                    $payItems[] = ['id' => $ordersRailway->orderId, 'type' => 'railway'];
                    $ordersIds[] = $ordersRailway->orderId;
                    $type[$ordersRailway->orderId] = 'railway';

                    if ($authorized_user === false) {
                        $userId = self::addUser($ordersRailway->passengersData, $request);
                        $ordersRailway->userId = $userId;
                        $ordersRailway->save();
                        $orders->userId = $userId;
                        $orders->save();
                    }

                    // добовляем пасажира
                    if (isset($request->Passengers)) {

                        foreach ($request->Passengers as $row) {

                            self::addPassenger($row, $userId);
                        }
                    }

                    $complexOrderId = $ordersRailway->complexOrderId;
                    $items[] = self::getItemFormat($ordersRailway, 'railway');

                    break;

                case 'aeroexpress':

                    $ordersAeroexpress = OrdersAeroexpress::where('orderId', $request->OrderId)->first();

                    if (!$ordersAeroexpress) {

                        SessionLog::orderLog($request->OrderId, 'pay',  'Заказа Аэроэкспресс с OrderId ' . $request->OrderId . ' не найден', true, StringHelpers::ObjectToArray($request));

                        return ResponseHelpers::jsonResponse([
                            'error' => LangHelper::trans('errors.order_not_found')
                        ], 404);
                    }

                    $sum = $ordersAeroexpress->Amount;
                    $amount += $sum;
                    $payItems[] = ['id' => $ordersAeroexpress->orderId, 'type' => 'aeroexpress'];
                    $ordersIds[] = $ordersAeroexpress->orderId;
                    $type[$ordersAeroexpress->orderId] = 'aeroexpress';

                    if ($authorized_user === false) {
                        $userId = self::addUser($ordersAeroexpress->passengersData, $request);
                        $ordersAeroexpress->userId = $userId;
                        $ordersAeroexpress->save();
                        $orders->userId = $userId;
                        $orders->save();
                    }

                    // добовляем пасажира
                    if (isset($request->Passengers)) {

                        foreach ($request->Passengers as $row) {

                            self::addPassenger($row, $userId);
                        }
                    }

                    $complexOrderId = $ordersAeroexpress->complexOrderId;
                    $items[] = self::getItemFormat($ordersAeroexpress, 'aeroexpress');

                    break;
            }
        }

        $userType = ($user && $user->role) ? 'operator' : 'client';
        $totalAmount = (int)$amount * 100;

        if ($totalAmount == 0) {
            SessionLog::orderLog($request->OrderId, 'pay',  'Нет заявок для оплаты', true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse([
                'error' => LangHelper::trans('errors.no_equests_for_payment')
            ], 406);
        }

        $parameter = [
            'pan' => $request->PayInfo['PAN'],
            'orderId' => $request->OrderId,
            'amount' => $totalAmount,
            'email' => $user->email,
            'cardname' => $request->PayInfo['CardHolder'],
            'expiryYear' => $request->PayInfo['EYear'],
            'expiryMonth' => $request->PayInfo['EMonth'],
            'cvc2' => $request->PayInfo['SecureCode'],
        ];

        $doPay = new DmsPayFacade($parameter);

        $payment = OrdersPayment::create([
            'userId' => $userId,
            'clientId' => $user ? $user->clientId : 0,
            'holdingId' => $user ? $user->holdingId : 0,
            'payItems' => $payItems,
            'complexOrderId' => $complexOrderId,
            'provider' => $userType == 'client' ? $doPay->provider : OrdersPayment::ORDER_PROVIDER_TRIVAGO,
            'type' => 'purchase',
            'request' => $request->except(['PayInfo.PAN', 'PayInfo.SecureCode']),
            'transactionId' => 'NO',
            'amount' => (double) $totalAmount / 100,
            'response' => null,
        ]);

        $doPay->orderId = $request->OrderId . '-' . $payment->id;

        if ($userType == 'client') {

            if ($doPay->block() === false) {
                $payment->status = $payment::STATUS_ERROR;
                $payment->response = $doPay::getResponse();
                $payment->save();

                return ResponseHelpers::jsonResponse([
                    'error' => $doPay::getError()
                ], 500);
            }

            $response = $doPay::getResponse();

            switch ($doPay->provider){
                case 'payture':
                    if($response['Success']==='3DS'){
                        return ResponseHelpers::jsonResponse([
                            'redirectURL' => $response['ACSUrl'],
                            'method' => 'POST',
                            'formData' => [
                                'TermUrl' =>  url('/api/v1/payments/secure',['system' => 'payture', 'transactionId' => $doPay->getTransId()]),
                                'MD' => $response['ThreeDSKey'],
                                'PaReq' => $response['PaReq']
                            ]
                        ],202);
                    }
                    break;
                case 'rsb':
                    $doPay->status();
                    $response = $doPay::getResponse();

                    if($response['3DSECURE']==='FAILED'){
                        return ResponseHelpers::jsonResponse([
                            'redirectURL' => config('trivago.services.rsb.redirectUrl').$doPay->getTransId(),
                            'method' => 'GET',
                            'formData' => null
                        ],202);
                    }
            }

            $payment->response = $doPay::getResponse();
            $payment->transactionId = $doPay->getTransId();
            $payment->save();
        }

        $orderResponse = [];
        $orderConfirmResults = [];
        $orderCustomers = [];

        // Подтверждаем бронь
        foreach ($ordersIds as $ordersId) {
            $confirmResponse = self::confirm($ordersId, $userId, $type[$ordersId]);

            // если произошла ошибка, разблокируем средства
            if ($userType == 'client' && (isset($confirmResponse['error']) || is_null($confirmResponse))) {

                $doPay->reverse();

                $payment->status = $payment::STATUS_REVERSED;
                $payment->response = $doPay::getResponse();
                $payment->save();

                if (is_null($confirmResponse)) {
                    SessionLog::orderLog($request->OrderId, 'pay', 'ЖД заказ с OrderId ' . $request->OrderId . ' не найден', true, StringHelpers::ObjectToArray($request));

                    return ResponseHelpers::jsonResponse([
                        'error' => LangHelper::trans('errors.order_not_found')
                    ], 404);
                }

                SessionLog::orderLog($request->OrderId, 'pay', $confirmResponse['error'], true, StringHelpers::ObjectToArray($request));

                return ResponseHelpers::jsonResponse([
                    'error' => $confirmResponse['error']
                ], 500);
            }

            $orderResponse[$ordersId] = $confirmResponse;
            $orderConfirmResults[] = $confirmResponse;
            $orderCustomers[$ordersId] = $confirmResponse;
        }

        if ($userType == 'client') {
            if ($doPay->charge() === false) {

                $payment->status = $payment::STATUS_ERROR;
                $payment->response = $doPay::getResponse();
                $payment->save();

                SessionLog::orderLog($request->OrderId, 'pay', $doPay::getError(), true,
                    StringHelpers::ObjectToArray($request));

                return ResponseHelpers::jsonResponse([
                    'error' => $doPay::getError()
                ], 500);
            }
            $payment->response = $doPay::getResponse();
        }

        $payment->status = $payment::STATUS_FINISHED;
        $payment->save();

        $payInfo = $request->PayInfo;

        if (isset($payInfo['PAN'])) {
            unset($payInfo['PAN']);
        }

        $request->PayInfo = $payInfo;

        if ($orders->id == $request->OrderId) {
            NotificationHelpers::PayAllOrders($request->OrderId,$user->email);
        } else {
            NotificationHelpers::PayOneOrder($request->OrderId, $orders["orderItems"][0]->type, $user->email);
        }

        NotificationHelpers::RegistrationViaOrder($userId);

        SessionLog::orderLog($request->OrderId, 'pay', 'Оплата заказа выполнена', false);

        return ResponseHelpers::jsonResponse([
            'OrderId' => $request->OrderId,
            'Customers' => $authorized_user === false ? $orderCustomers : null,
            'items' => $items,
            'ConfirmResults' => $orderConfirmResults
        ]);
    }

    public function finish3DS(Request $request, $system, $transactionId=false)
    {
        switch ($system) {
            case 'payture':

                $MD = $request->get('MD');
                $paRes = $request->get('PaRes');

                $dsParams = ['paRes' => $paRes];

                $payment = OrdersPayment::where('transactionId', $transactionId)->first();

                if (!$payment) {
                    if (!$payment) {
                        return response($this->invokeFrontResponse('do3DSFinish',null, [
                            'message' => LangHelper::trans('transactions.trans_not_found')
                        ]));
                    }
                }

                $parameter = [
                    'orderId' => $transactionId,
                    'provider' => 'payture',
                ];

                $doPay = new DmsPayFacade($parameter);

                if(!$doPay->block3DS($dsParams)){
                    return response($this->invokeFrontResponse('do3DSFinish',null, [
                        'message' => $doPay::getError()
                    ]));
                };
                break;

            case 'rsb':

                $transactionId = $request->get('trans_id');
                $payment = OrdersPayment::where('transactionId', $transactionId)->first();

                if (!$payment) {
                    return response($this->invokeFrontResponse('do3DSFinish',null, [
                        'message' => LangHelper::trans('transactions.trans_not_found')
                    ]));
                }

                $dsParams = [];

                $parameter = [
                    'orderId' => $transactionId,
                    'provider' => 'rsb',
                ];

                $doPay = new DmsPayFacade($parameter);

                if(!$doPay->block3DS($dsParams)){
                    return response($this->invokeFrontResponse('do3DSFinish',null, [
                        'message' => $doPay::getError()
                    ]));
                };

                break;
            default:

                return response($this->invokeFrontResponse('do3DSFinish',null, [
                    'message' => LangHelper::trans('transactions.wrong_payment_system')
                ]));

        }

        $orderResponse = [];
        $orderConfirmResults = [];
        $orderCustomers = [];
        $items = [];

        $order = $payment->order();

        foreach ($payment->payItems as $payItem) {
            $confirmResponse = self::confirm($payItem->id, $payment->userId, $payItem->type);


            // если произошла ошибка, разблокируем средства
            if (isset($confirmResponse['error']) || is_null($confirmResponse)) {

                $doPay->reverse();

                $payment->status = $payment::STATUS_REVERSED;
                $payment->response = $doPay::getResponse();
                $payment->save();


                if (is_null($confirmResponse)) {
                    return response($this->invokeFrontResponse('do3DSFinish',null, [
                        'message' => LangHelper::trans('errors.order_not_found')
                    ]));
                }
            }

            $ordersData = $order->getItemById($payItem->id);
            $items[] = self::getItemFormat($ordersData, $payItem->type);

            $orderResponse[$payItem->id] = $confirmResponse;
            $orderConfirmResults[] = $confirmResponse;
            $orderCustomers[$payItem->id] = $confirmResponse;
        }

        if ($doPay->charge() === false) {

            $payment->status = $payment::STATUS_ERROR;
            $payment->response = $doPay::getResponse();
            $payment->save();

            return response($this->invokeFrontResponse('do3DSFinish',null, [
                'message' => $doPay::getError()
            ]));

        }

        $payment->status = $payment::STATUS_FINISHED;
        $payment->response = $doPay::getResponse();
        $payment->save();

        if (count($payment->payItems) > 1) {
            NotificationHelpers::PayAllOrders($order->id, $payment->user->email);
        } else {
            NotificationHelpers::PayOneOrder($payment->payItems[0]->id, $payment->payItems[0]->type,
                $payment->user->email);
        }

        return response($this->invokeFrontResponse('do3DSFinish',[
            'OrderId' => $order->id,
            'Customers' =>  $orderCustomers,
            'items' => $items,
            'ConfirmResults' => $orderConfirmResults
        ], null));

    }

    private function invokeFrontResponse($func, $data, $err)
    {
        $data = json_encode($data,JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $err = json_encode($err, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return "<body><script>
var data = {$data};
var err = {$err};
window.opener.{$func}(data, err);
</script></body>";
    }

    /**
     * @param array $passengersData
     * @param $email
     * @return mixed
     */
    protected static function addUser($passengersData, $request)
    {
        $passengersData = StringHelpers::ObjectToArray($passengersData);

        $contact = [];

        foreach ($passengersData as $data) {
            if (isset($data['ContactEmail']) && $data['ContactEmail'] == $request->Customer['email']) {
                $contact['ContactEmails'] = $request->Customer['email'];
                $contact['FirstName'] = isset($data['FirstName']) ? $data['FirstName'] : '';
                $contact['LastName'] = isset($data['LastName']) ? $data['LastName'] : '';
                $contact['MiddleName'] = isset($data['MiddleName']) ? $data['MiddleName'] : '';
                $contact['ContactPhone'] = isset($data['ContactPhone']) ? $data['ContactPhone'] : '';

                break;
            }
        }

        $user = User::where('email', 'ilike', $request->Customer['email'])->first();

        if (!$user) {
            $u_data['userTypeId'] = 1;
            $u_data['email'] = $request->Customer['email'];
            $u_data['login'] = $request->Customer['email'];
            $u_data['password'] = '';
            $u_data['mobile'] = isset($contact['ContactPhone']) ? $contact['ContactPhone'] : '';
            $u_data['contacts'] = [
                'ContactEmails' => $request->Customer['email'],
                'ContactPhone' => isset($contact['ContactPhone']) ? $contact['ContactPhone'] : '',
                'FirstName' => isset($contact['FirstName']) ? $contact['FirstName'] : '',
                'LastName' => isset($contact['LastName']) ? $contact['LastName'] : '',
                'MiddleName' => isset($contact['MiddleName']) ? $contact['MiddleName'] : '',
            ];

            $u_data['lastAccessIp'] = $request->ip();

            $userId = User::create($u_data)->userId;

        } else {
            $userId = $user->userId;
        }



        return $userId;
    }

    /**
     * @param array $row
     * @param $userId
     * @param int $holdingId
     * @param int $clientId
     * @return mixed
     */
    protected static function addPassenger(array $row, $userId, $holdingId = 0, $clientId = 0)
    {

        $data = [
            'contacts' => [
                'phone' => isset($row['phone']) ? $row['phone'] : null,
                'email' => isset($row['email']) ? $row['email'] : null,
                'sex' => isset($row['sex']) ? $row['sex'] : null,
                'birthDate' => isset($row['birthDate']) ? $row['birthDate'] : null,
            ],
            'cards' => isset($row['cards']) ? $row['cards'] : [],
            'documents' => isset($row['documents']) ? $row['documents'] : null,
            'nameRu' => [
                'firstName' => isset($row['nameRu']['firstName']) ? $row['nameRu']['firstName'] : null,
                'lastName' => isset($row['nameRu']['lastName']) ? $row['nameRu']['lastName'] : null,
                'middleName' => isset($row['nameRu']['middleName']) ? $row['nameRu']['middleName'] : null
            ],
            'nameEn' => [
                'firstName' => isset($row['nameEn']['firstName']) ? $row['nameEn']['firstName'] : null,
                'lastName' => isset($row['nameEn']['lastName']) ? $row['nameEn']['lastName'] : null,
                'middleName' => isset($row['nameEn']['middleName']) ? $row['nameEn']['middleName'] : null
            ]
        ];

        $passenger = Passengers::where('userId', $userId)->where('nameRu->firstName', $data['nameRu']['firstName'])->where('nameRu->lastName', $data['nameRu']['lastName'])->first();

        if(!$passenger) {
            return Passengers::create([
                'userId' => $userId,
                'holdingId' => $holdingId,
                'clientId' => $clientId,
                'contacts' => $data['contacts'],
                'documents' => $data['documents'],
                'cards' => $data['cards'],
                'nameRu' => $data['nameRu'],
                'nameEn' => $data['nameEn']
            ])->passengerId;
        }
        return $passenger->passengerId;
    }

    /**
     * @queryParam $orderId
     * @queryParam int $userId
     * @return array
     */
    public static function confirm($orderId, $userId = 0, $type)
    {
        if (!empty($type)) {
            switch ($type) {
                case 'railway':
                    $ordersRailway = OrdersRailway::where('orderId', $orderId)->first();
                    break;
                case 'aeroexpress':
                    $ordersRailway = OrdersAeroexpress::where('orderId', $orderId)->first();
                    break;
            }
        }

        if ($ordersRailway) {

            $orderResponse = OrderReservation::doConfirm(['OrderId' => $ordersRailway->orderData->orderId, 'OrderCustomerIds' => null, 'OrderCustomerDocuments' => null, 'ProviderPaymentForm' => 'Card']);

            if (!$orderResponse) {
                SessionLog::orderLog($orderId, 'confirm', OrderReservation::getLastError()->Message, true, ['OrderId' => $ordersRailway->first()->orderData->orderId, 'OrderCustomerIds' => null, 'OrderCustomerDocuments' => null, 'ProviderPaymentForm' => 'Card']);
                return ['error' => OrderReservation::getLastError()];
            }

            $orderResponse = StringHelpers::ObjectToArray($orderResponse);

            $blanks = null;

            $orderData = $ordersRailway->orderData;
            if (isset($orderResponse["ConfirmResults"]) && is_array($orderResponse["ConfirmResults"])) {
                foreach ($orderResponse["ConfirmResults"] as $confirmResults) {
                    if (isset($confirmResults["Blanks"])) {
                        foreach ($confirmResults["Blanks"] as $blank) {
                            $blanks[] = $blank;
                        }
                    }
                }
                $orderData->result->ConfirmResults = $orderResponse['ConfirmResults'];
            }

            if ($ordersRailway->orderStatus == 0 || $ordersRailway->orderStatus == 1) {
                $ordersRailway->update([
                    'orderStatus' => OrdersRailway::ORDER_STATUS_COMPLETED,
                    'orderDocuments' => $blanks,
                    'orderData' => $orderData
                ]);
            }

            SessionLog::orderLog($orderId, 'confirm', 'Подтверждение заказа', false);

            return $orderResponse;
        } else {
            return null;
        }
    }

    /**
     * @param $ordersRailway
     * @param $type
     * @return array
     */
    private static function getItemFormat($item, $type)
    {
        switch ($type) {
            case 'railway':
                $status = OrdersRailway::$status_name[$item->orderStatus];
                break;
            case 'aeroexpress':
                $status = OrdersAeroexpress::$status_name[$item->orderStatus];
                break;
        }

        return [
            'id' => $item->orderId,
            'type' => $type,
            'holdingId' => $item->holdingId,
            'orderStatus' => $item->orderStatus,
            'status' => $status,
            'passengersData' => $item->passengersData,
            'orderData' => isset($item->orderData->result) ? $item->orderData->result : null,
            'provider' => $item->provider,
            'orderDocuments' => $item->orderDocuments,
            'Amount' => $item->Amount,
            'created_at' => $item->created_at,
            'created' => DateTimeHelpers::dateFormat(DateTimeHelpers::convertDate($item->created_at)),
        ];
    }

    /**
     * Pay
     * [Доплата по заявке]
     * @bodyParam OrderId integer required Уникальный идентификатор платежа в системе ТСП
     * @bodyParam PayInfo array Параметры для совершения транзакции
     * @bodyParam email string required mail куда мы отправим подтверждение, что платеж пришел
     * @bodyParam amount integer required сумма для оплаты заявки
     *
     * @response {
     * {
     * "OrderId": 605,
     * "Customers": [],
     * "items": []
     * }
     *
     * @response 404 {
     * "orders": null
     * }
     *
     * @response 401 {
     * "error": true,
     * "message": "Auth required"
     * }
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getQuickPay(Request $request)
    {

        $user = \Auth::user('web');

        // проверяем авторизован ли пользователь
        if (!$user) {
            return ResponseHelpers::jsonResponse(['error' => true, 'message' => 'Auth required'], 401);
        }

        $orders = Orders::GetById($request->OrderId)->first();

        if (!$orders) {
            return ResponseHelpers::jsonResponse([
                'error' => LangHelper::trans('errors.order_not_found')
            ], 404);
        }

        $validator = Validator::make($request->all(),
            [
                'OrderId' => 'required|numeric',
                'PayInfo' => 'required|array',
                'email' => 'required|email',
                'amount' => 'required|numeric',
            ],
            [
                'amount.required' => 'Не указана сумма к оплате заявки!',
                'PayInfo.required' => 'Не указаны параметры для совершения транзакции!',
                'OrderId.required' => 'Не указан идентификатор платежа в системе ТСП!',
                'OrderId.numeric' => 'Не верно указан идентификатор платежа в системе ТСП!',
            ]
        );

        if ($validator->fails()) {
            return ResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);
        }

        $user = \Auth::user('web');

        // проверяем авторизован ли пользователь
        if (!$user) {
            SessionLog::orderLog($request->OrderId, 'quickpay', 'Пользователь не авторизован', true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse(['error' => true, 'message' => 'Auth required'], 401);
        }

        $orders = Orders::GetById($request->OrderId)->first();

        if (!$orders) {
            SessionLog::orderLog($request->OrderId, 'quickpay', 'Заказ с OrderId ' . $request->OrderId . ' не найден', true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse([
                'error' => LangHelper::trans('errors.order_not_found')
            ], 404);
        }

        $items = [];
        $ordersIds = [];

        if ($orders->id == $request->OrderId) {
            // оплачиваем весь заказ

            $complexOrderId = $orders->id;

            foreach ($orders["orderItems"] as $order) {
                switch ($order->type) {
                    case 'railway':

                        $ordersData = $orders->getItemById($order->id);
                        $payItems[] = ['id' => $order->id, 'type' => $order->type];
                        $ordersIds[] = $order->id;
                        $type[$order->id] = $order->type;
                        $items[] = self::getItemFormat($ordersData, $order->type);

                        break;
                }
            }

        } else {
            switch ($orders["orderItems"][0]->type) {
                case 'railway':

                    $ordersRailway = OrdersRailway::where('orderId', $request->OrderId)->first();

                    if (!$ordersRailway) {
                        SessionLog::orderLog($request->OrderId, 'quickpay', 'ЖД заказ с OrderId ' . $request->OrderId . ' не найден', true, StringHelpers::ObjectToArray($request));

                        return ResponseHelpers::jsonResponse([
                            'error' => LangHelper::trans('errors.order_not_found')
                        ], 404);
                    }

                    $payItems[] = ['id' => $ordersRailway->orderId, 'type' => 'railway'];
                    $ordersIds[] = $ordersRailway->orderId;
                    $type[$ordersRailway->orderId] = 'railway';

                    $complexOrderId = $ordersRailway->complexOrderId;
                    $items[] = self::getItemFormat($ordersRailway, 'railway');

                    break;

                case 'aeroexpress':

                    $ordersAeroexpress = OrdersAeroexpress::where('orderId', $request->OrderId)->first();

                    if (!$ordersAeroexpress) {
                        SessionLog::orderLog($request->OrderId, 'quickpay', 'Aроэкспресс заказ с OrderId ' . $request->OrderId . ' не найден', true, StringHelpers::ObjectToArray($request));

                        return ResponseHelpers::jsonResponse([
                            'error' => LangHelper::trans('errors.order_not_found')
                        ], 404);
                    }

                    $payItems[] = ['id' => $ordersAeroexpress->orderId, 'type' => 'aeroexpress'];
                    $ordersIds[] = $ordersAeroexpress->orderId;
                    $type[$ordersAeroexpress->orderId] = 'aeroexpress';

                    $complexOrderId = $ordersAeroexpress->complexOrderId;
                    $items[] = self::getItemFormat($ordersAeroexpress, 'aeroexpress');

                    break;
            }
        }

        $totalAmount = (int)$request->amount * 100;

        $parameter = [
            'pan' => $request->PayInfo['PAN'],
            'orderId' => $request->OrderId,
            'amount' => $totalAmount,
            'email' => $user->email,
            'cardname' => $request->PayInfo['CardHolder'],
            'expiryYear' => $request->PayInfo['EYear'],
            'expiryMonth' => $request->PayInfo['EMonth'],
            'cvc2' => $request->PayInfo['SecureCode'],
        ];

        $doPay = new DmsPayFacade($parameter);


        if ($doPay->pay() === false)  {
            SessionLog::orderLog($request->OrderId, 'quickpay', $doPay::getError(), true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse([
                'error' => $doPay::getError()
            ], 500);
        }

        $orderResponse = [];
        $orderConfirmResults = [];
        $orderCustomers = [];

        // Подтверждаем бронь
        foreach ($ordersIds as $ordersId) {
            $confirmResponse = self::confirm($ordersId, $user->userId, $type[$ordersId]);

            // если произошла ошибка, разблокируем средства
            if (isset($confirmResponse['error']) || is_null($confirmResponse)) {

                $doPay->reverse();

                if (is_null($confirmResponse)) {
                    SessionLog::orderLog($request->OrderId, 'quickpay', 'Заказ с OrderId ' . $request->OrderId . ' не найден', true, StringHelpers::ObjectToArray($request));

                    return ResponseHelpers::jsonResponse([
                        'error' => LangHelper::trans('errors.order_not_found')
                    ], 404);
                }

                SessionLog::orderLog($request->OrderId, 'quickpay', $confirmResponse['error'], true, StringHelpers::ObjectToArray($request));

                return ResponseHelpers::jsonResponse([
                    'error' => $confirmResponse['error']
                ], 500);
            }

            $orderResponse[$ordersId] = $confirmResponse;
            $orderConfirmResults[] = $confirmResponse;
            $orderCustomers[$ordersId] = $confirmResponse;
        }

        if ($doPay->charge() === false) {
            SessionLog::orderLog($request->OrderId, 'quickpay', $doPay::getError(), true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse([
                'error' => $doPay::getError()
            ], 500);
        }

        foreach ($ordersIds as $ordersRailwayId) {
            if (isset($type[$ordersRailwayId]) && $type[$ordersRailwayId] == 'railway') {
                QueueBalanced::balance(new Soap1CRailwayOrderPush($ordersRailwayId, $type[$ordersRailwayId]), 'one-c');
            }
        }

        $payInfo = $request->PayInfo;

        if (isset($payInfo['PAN'])) {
            unset($payInfo['PAN']);
        }

        $request->PayInfo = $payInfo;

        OrdersPayment::create([
            'payItems' => $payItems,
            'complexOrderId' => $complexOrderId,
            'provider' => OrdersPayment::ORDER_PROVIDER_PAYTURE,
            'type' => 'purchase',
            'request' => $request,
            'response' => isset($response) ? $response : null,
        ]);

        Orders::GetById($request->OrderId)->update(['userId' => $user->userId]);

        if ($orders->id == $request->OrderId) {
            NotificationHelpers::PayAllOrders($request->OrderId, $request->email);
        } else {
            NotificationHelpers::PayOneOrder($request->OrderId, $orders["orderItems"][0]->type, $request->email);
        }

        SessionLog::orderLog($request->OrderId, 'quickpay', 'Доплата по заявке выполнена', false);

        return ResponseHelpers::jsonResponse([
            'OrderId' => $request->OrderId,
            'items' => $items,
            'ConfirmResults' => $orderConfirmResults
        ]);
    }

    /**
     * getPayLinks
     * [Формирование ссылок клиенту на доплату]
     * @bodyParam orderId numeric required номер заявки
     * @bodyParam amount numeric required сумму платежа
     * @bodyParam email required email куда мы отправим подтверждение, что платеж пришел
     * @bodyParam language язык
     * @response {
     * "OrderId": 238,
     * "еmail": "alexander.yanitsky@trivago.ru",
     * "amount": 42355,
     * "language": "ru"
     * }
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getPayLinks(Request $request)
    {

        $user = \Auth::user('web');

        // проверяем авторизован ли пользователь
        if (!$user) {
            return ResponseHelpers::jsonResponse(['error' => true, 'message' => 'Auth required'], 401);
        }

        // проверяем, является ли пользоваеть оператором
        if (!$user->role) {
            return ResponseHelpers::jsonResponse(['error' => true, 'message' => 'Access denied'], 403);
        }

        $validator = Validator::make($request->all(),
            [
                'orderId' => 'required|numeric',
                'amount' => 'required|numeric',
                'email' => 'required|email',
            ],
            [
                'orderId.required' => 'Введите номер заявки',
                'orderId.numeric' => 'Не верно указан номер заявки',
                'amount.required' => 'Введите сумму платежа',
                'amount.numeric' => 'Не верно указана сумму платежа',
            ]
        );

        if ($validator->fails()) {
            return ResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);
        }

        $user = \Auth::user('web');

        // проверяем авторизован ли пользователь
        if (!$user) {

            SessionLog::orderLog($request->OrderId, 'quickpay', 'Пользователь не авторизован', true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse(['error' => true, 'message' => 'Auth required'], 401);
        }

        // проверяем, является ли пользоваеть оператором
        if (!$user->role) {
            SessionLog::orderLog($request->OrderId, 'quickpay', 'Доступ разрешен только операторам', true, StringHelpers::ObjectToArray($request));
            return ResponseHelpers::jsonResponse(['error' => true, 'message' => 'Access denied'], 403);
        }

        $orders = Orders::GetById($request->orderId)->first();

        if (!$orders) {

            SessionLog::orderLog($request->OrderId, 'quickpay', 'Заказ с OrderId ' . $request->OrderId . ' не найден', true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse([
                'error' => LangHelper::trans('errors.order_not_found')
            ], 404);
        }

        if ($request->has('language') && !empty($request->language)) {
            $language = $request->language;
        } else {
            $language = 'ru';
        }

        $settings = new Settings();

        $payLinks[] = [
            'url' => URL::route('api.v1.payments.quickpay') . '?' . http_build_query([
                    'orderId' => $request->orderId,
                    'amount' => $request->amount,
                    'language' => $language,
                    'email' => $request->email
                ]),
            'type' => 'payture',
            'name' => 'Payture - ' . $settings->get('taxPayture') . ' % Оплатить ' . round(($request->amount + $request->amount * ($settings->get('taxPayture') / 100)),
                    2) . ' руб'
        ];

        $payLinks[] = [
            'url' => URL::route('api.v1.payments.quickpay') . '?' . http_build_query([
                    'orderId' => $request->orderId,
                    'amount' => $request->amount,
                    'language' => $language,
                    'email' => $request->email
                ]),
            'type' => 'rsb',
            'name' => 'RSB - ' . $settings->get('taxRSB') . ' % Оплатить ' . round(($request->amount + $request->amount * ($settings->get('taxRSB') / 100)),
                    2) . ' руб'
        ];

        SessionLog::orderLog($request->OrderId, 'quickpay', 'Формирование ссылок клиенту на доплату', false);

        return ResponseHelpers::jsonResponse([
            'message' => '<h3>Сумма к оплате без учета комиссии: ' . $request->amount . ' руб. </h3>Оплата при помощи банковской карты. Комиссия зависит от выбора типа карты:',
            'payLinks' => $payLinks
        ], 200);
    }
}