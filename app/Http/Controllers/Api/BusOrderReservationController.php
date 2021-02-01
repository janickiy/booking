<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\AppException;
use App\Http\Requests\Api\V1\Aeroexpress\Reservation\AutoReturnRequest;
use App\Http\Requests\Api\V1\Bus\Reservation\ConfirmRequest;
use App\Http\Requests\Api\V1\Bus\Reservation\CreateRequest;
use App\Http\Requests\Api\V1\Bus\Reservation\ReturnAmountRequest;
use App\Models\OrdersAeroexpress;
use App\Models\OrdersBus;
use App\Models\Orders;
use App\Helpers\StringHelpers;
use App\Services\External\InnovateMobility\v1\OrderReservation;
use Illuminate\Support\Collection;
use Throwable;

/**
 * Class BusOrderReservationController
 * [Методы для осуществления (бронирования/возврата/обмена) электронных автобусных билетов]
 * @group BusOrderReservationController
 * @package App\Http\Controllers\Api
 */
class BusOrderReservationController extends AbstractOrderReservationController
{
    protected static $customerType = 'ApiContracts.Order.V1.Reservation.OrderFullCustomerRequest, ApiContracts';
    protected static $reservationType = 'ApiContracts.Bus.V1.Messages.Reservation.BusReservationRequest, ApiContracts';
    protected static $autoReturnType = 'ApiContracts.Bus.V1.Messages.Return.BusAutoReturnRequest, ApiContracts';
    protected static $returnAmountType = 'ApiContracts.Bus.V1.Messages.Return.BusReturnAmountRequest, ApiContracts';

    /**
     * BusOrderReservationController constructor.
     */
    public function __construct()
    {
        $this->setModel(new OrdersBus());
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
            $ordersRailway = OrdersBus::create([
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
                    'result'        => $result
                ],
                'orderDocuments'    => $blanks,
                'complexOrderId'    => $orders->id,
                'payTill'           => str_replace('T', '', $result['ConfirmTill']),
            ]);

            $orderItems[] = ['id' => $ordersRailway->orderId, 'type' => Orders::ORDER_TYPE_BUS];
            Orders::where('id', $orders->id)->update([
                'orderItems' => json_encode($orderItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ]);
            $response = StringHelpers::ObjectToArray($response);

            return $this->successResponse(
                [
                    'id'                    => $ordersRailway->orderId,
                    'complexOrderId'        => $orders->id,
                    'type'                  => 'bus',
                    'Amount'                => $response['Amount'],
                    'ConfirmTill'           => $response['ConfirmTill'],
                    'Customers'             => $response['Customers'],
                    'ReservationResults'    => $response['ReservationResults'],
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
     * @param AutoReturnRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getAutoReturn(AutoReturnRequest $request)
    {
        try {
            $serviceAutoReturnRequest = array_merge(
                ['$type' => self::$autoReturnType],
                ['OrderItemId' => $request->input('OrderItemId')]
            );
            $response = OrderReservation::getAutoReturn(['ServiceAutoReturnRequest' => $serviceAutoReturnRequest]);

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

    /**
     * Confirm
     * [Подтверждение бронирования]
     * https://test.onelya.ru/ApiDocs/Api?apiId=Order-V1-Reservation-Confirm
     *
     * @bodyParam OrderId integer required Идентификатор заказа
     * @param ConfirmRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getConfirm(ConfirmRequest $request)
    {
        try {
            $response = OrderReservation::doAutoReturn(['OrderId' => $request->input('OrderId')]);

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

    /**
     * ReturnAmount
     * [Получение суммы планируемого автоматического возврата]
     * https://test.onelya.ru/ApiDocs/Api?apiId=Order-V1-Reservation-ReturnAmount
     * @bodyParam ServiceReturnAmountRequest sting required Входные данные на возврат услуги по отдельной позиции заказа
     * @param ReturnAmountRequest $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getReturnAmount(ReturnAmountRequest $request)
    {
        try {
            $serviceAutoReturnRequest = array_merge(
                ['$type' => self::$autoReturnType],
                ['OrderItemId' => $request->input('OrderItemId')],
                ['CheckDocumentNumber' => $request->input('CheckDocumentNumber')]
            );
            $response = OrderReservation::getReturnAmount(['ServiceAutoReturnRequest' => $serviceAutoReturnRequest]);

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
