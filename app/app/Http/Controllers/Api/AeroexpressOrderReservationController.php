<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\AppException;
use App\Helpers\ResponseHelpers;
use App\Http\Requests\Api\V1\Aeroexpress\Reservation\AutoReturnRequest;
use App\Http\Requests\Api\V1\Aeroexpress\Reservation\CreateRequest;
use App\Jobs\Soap1CAeroexpressOrderRefund;
use App\Jobs\Soap1CRailwayOrderRefund;
use App\Models\OrdersAeroexpress;
use App\Models\OrdersPayment;
use App\Services\External\InnovateMobility\v1\OrderInfo;
use Illuminate\Http\Request;
use App\Models\Orders;
use App\Helpers\StringHelpers;
use App\Services\External\InnovateMobility\v1\OrderReservation;
use Illuminate\Support\Collection;
use Throwable;

/**
 * Class AeroexpressOrderReservationController
 * [Методы для работы с заказом Aeroexpress(бронирование/возврат/обмен)]
 * @group AeroexpressOrderReservation
 * @package App\Http\Controllers\Api
 */
class AeroexpressOrderReservationController extends AbstractOrderReservationController
{
    protected static $customerType = 'ApiContracts.Order.V1.Reservation.OrderFullCustomerRequest, ApiContracts';
    protected static $reservationType = 'ApiContracts.Aeroexpress.V1.Messages.Reservation.AeroexpressReservationRequest, ApiContracts';
    protected static $autoReturnType = 'ApiContracts.Aeroexpress.V1.Messages.Return.AeroexpressAutoReturnRequest, ApiContracts';

    /**
     * AeroexpressOrderReservationController constructor.
     */
    public function __construct()
    {
        $this->setModel(new OrdersAeroexpress());
        parent::__construct();
    }

    /**
     * Create
     * [Выдаём данные для создания бронирования]
     * @param CreateRequest $request
     * https://test.onelya.ru/ApiDocs/Api?apiId=Order-V1-Reservation-Create
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     * @bodyParam ContactPhone string Контактный телефон
     * @bodyParam ContactEmails array Контактные емэйлы
     * @bodyParam Customers array required Пользователи услуг заказа
     * @bodyParam ReservationItems array required Услуги на бронирование
     */
    public function getCreate(CreateRequest $request)
    {
        try {
            $user = \Auth::user('web');
            $userId = isset($user->userId) ? $user->userId : 0;

            $customers = array_map(function ($customer) {
                return array_merge(['$type' => self::$customerType], $customer);
            }, $request->input('Customers'));

            $reservations = array_map(function ($reservationItem) {
                return array_merge(['$type' => self::$reservationType], $reservationItem);
            }, $request->input('ReservationItems'));

            $response = OrderReservation::doCreate([
                'ContactPhone'      => $request->input('ContactPhone'),
                'ContactEmails'     => $request->input('ContactEmails'),
                'Customers'         => $customers,
                'ReservationItems'  => $reservations
            ]);

            if (!$response) {
                throw new AppException(OrderReservation::getLastError()->Message);
            }

            $result = StringHelpers::ObjectToArray($response);

            $orderInfo = OrderInfo::doOrderInfo([
                'OrderId' => $result['OrderId'],
                'AgentReferenceId' => null
            ]);

            if (!$orderInfo) {
                throw new AppException(OrderInfo::getLastError()->Message);
            }

            $blanks = null;
            $passengers = null;
            if (isset($result["ReservationResults"]) && is_array($result["ReservationResults"])) {
                $collection = new Collection($result["ReservationResults"]);
                $passengers = $collection->pluck('Passengers')->collapse()->toArray();
                $blanks = $collection->pluck('Blanks')->collapse()->toArray();
            }
            $orders = Orders::create([
                'userId'      => $userId
            ]);
            $ordersRailway = OrdersAeroexpress::create([
                'provider'          => 'IM',
                'userId'            => $userId,
                'Amount'            => $result['Amount'],
                'passengersData'    => $passengers,
                'orderStatus'       => OrdersAeroexpress::ORDER_STATUS_CREATED,
                'orderData'         => [
                    'orderId'       => $result['OrderId'],
                    'ContactPhone'  => isset($request->input('ContactPhone')[0]) ? $request->input('ContactPhone')[0] : null,
                    'ContactEmails' => isset($request->input('ContactEmails')[0]) ? $request->input('ContactEmails')[0] : null,
                    'Customers'     => $result['Customers'],
                    'result'        => $result,
                    'result2'       => $orderInfo,
                ],
                'orderDocuments'    => $blanks,
                'complexOrderId'    => $orders->id,
                'payTill'           => str_replace('T', '', $result['ConfirmTill']),
            ]);

            $orderItems[] = ['id' => $ordersRailway->orderId, 'type' => Orders::ORDER_TYPE_AEROEXPRESS];
            Orders::where('id', $orders->id)->update([
                'orderItems' => json_encode($orderItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ]);
            $response = StringHelpers::ObjectToArray($response);

            return $this->successResponse(
                [
                    'id'                    => $ordersRailway->orderId,
                    'complexOrderId'        => $orders->id,
                    'type'                  => 'aeroexpress',
                    'Amount'                => $response['Amount'],
                    'ConfirmTill'           => $response['ConfirmTill'],
                    'Customers'             => $response['Customers'],
                    'ReservationResults'    => $response['ReservationResults'],
                    'tax'                   => [
                        "rzhd" => getSetting('taxRailwayImRzdPurchase'),
                        "agent" => getSetting('taxRailwayImTrivagoPurchase'),
                        "total" => getSetting('taxRailwayImRzdPurchase')
                            + getSetting('taxRailwayImTrivagoPurchase')],
                ]
            );
        } catch (AppException $e) {
            return $this->appExceptionResponse($e);
        } catch (Throwable $e) {
            return $this->clientFriendlyExceptionResponse($e);
        }
    }

    /**
     * AutoReturn
     * [Проведение автоматического возврата]
     * https://test.onelya.ru/ApiDocs/Api?apiId=Order-V1-Reservation-AutoReturn
     *
     * @bodyParam ServiceAutoReturnRequest sting required Входные данные на возврат услуги по отдельной позиции заказа
     * TODO переписать(скопировал у Яницкого Александа)
     * @param AutoReturnRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getAutoReturn(AutoReturnRequest $request)
    {
        try {
            $user = $request->user('web');

            if (!$user) return ResponseHelpers::jsonResponse(['error' => true, 'message' => 'Auth required'], 401);
            $orderId = $request->input('OrderId');

            $order = $user->orders()->getById($orderId)->first();

            if (!$order) return ResponseHelpers::jsonResponse(['order' => null], 404);

            if ($order->id != $request->OrderId) {
                $items = $order->getItemById($orderId);
                if (!$items) return ResponseHelpers::jsonResponse(['order' => null], 404);
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

            $serviceAutoReturnRequest = array_merge(
                ['$type' => self::$autoReturnType],
                ['OrderItemId' => $orderData['result']['ReservationResults'][0]["OrderItemId"],]
            );
            $response = OrderReservation::doAutoReturn(['ServiceAutoReturnRequest' => $serviceAutoReturnRequest]);

            if (!$response) {
                throw new AppException(OrderReservation::getLastError()->Message);
            }

            $amount = isset($response->ServiceReturnResponse->Amount) ? (int)$response->ServiceReturnResponse->Amount * 100 : 0.00;

            $orderPayment = OrdersPayment::getById($orderId);
            if ($orderPayment->count() > 0) {
                switch ($orderPayment->first()->provider) {
                    case 'payture':
                        $responsePay = PaytureAPI::Refund($orderId, $amount, $request->Cheque);
                        $responsePay = StringHelpers::ObjectToArray($responsePay);

                        if (isset($responsePay['error'])) {
                            return ResponseHelpers::jsonResponse([
                                'error' => $responsePay['error']
                            ], $responsePay['code']);
                        }

                        OrdersPayment::create([
                            'payItems' => $orderPayment->first()->payItems,
                            'complexOrderId' => $orderPayment->first()->complexOrderId,
                            'provider' => 'payture',
                            'type' => 'refund',
                            'request' => json_encode(['orderId' => $orderId, 'amount' => $amount, 'Cheque' => $request->Cheque], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                            'response' => json_encode($responsePay, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)

                        ]);

                        break;
                }
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

            OrdersAeroexpress::where('orderId',$orderId)->update(['orderDocuments' => json_encode($blanks, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);

            return $this->successResponse($response);
        } catch (AppException $e) {
            return $this->appExceptionResponse($e);
        } catch (Throwable $e) {
            return $this->clientFriendlyExceptionResponse($e);
        }
    }

    /**
     * Void
     * [Аннулирование подтвержденного бронирования.]
     * https://test.onelya.ru/ApiDocs/Api?apiId=Order-V1-Reservation-Void
     *
     * @bodyParam ServiceAutoReturnRequest sting required Входные данные на возврат услуги по отдельной позиции заказа
     * @param AutoReturnRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getVoid(AutoReturnRequest $request)
    {
        try {
            $user = $request->user('web');

            if (!$user) return ResponseHelpers::jsonResponse(['error' => true, 'message' => 'Auth required'], 401);
            $orderId = $request->input('OrderId');

            $order = $user->orders()->getById($orderId)->first();

            if (!$order) return ResponseHelpers::jsonResponse(['order' => null], 404);

            if ($order->id != $request->OrderId) {
                $items = $order->getItemById($orderId);
                if (!$items) return ResponseHelpers::jsonResponse(['order' => null], 404);
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

            $response = OrderReservation::doVoid(['OrderId' => $orderData['orderId']]);

            if (!$response) {
                throw new AppException(OrderReservation::getLastError()->Message);
            }
            return $this->successResponse($response);
        } catch (AppException $e) {
            return $this->appExceptionResponse($e);
        } catch (Throwable $e) {
            return $this->clientFriendlyExceptionResponse($e);
        }
    }
}

