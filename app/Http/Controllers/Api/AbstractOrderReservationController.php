<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\OrdersRailway;
use App\Helpers\StringHelpers;
use App\Services\External\InnovateMobility\v1\OrderReservation;
use Illuminate\Support\Facades\Storage;

/**
 * @group AbstractOrderReservationController
 * [Абстрактный класс с методами для работы с заказом(бронирование/возврат/обмен)]
 * Class AbstractOrderReservationController
 * @package App\Http\Controllers\Api
 */
abstract class AbstractOrderReservationController extends BaseController
{

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    private $model;

    /**
     * AbstractOrderReservationController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Получение маршрут-квитанции
     * Если не указан OrderItemId или OrderItemIds — получение всех бланков по заказу, указанному в OrderId.
     * https://test.onelya.ru/ApiDocs/Api?apiId=Order-V1-Reservation-Blank
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response|void
     */
    public function getBlank(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'OrderId' => 'required|numeric',
                'OrderItemIds' => 'array|nullable',
                'RetrieveMainServices' => 'boolean',
                'RetrieveUpsales' => 'boolean'
            ],
            [
                'OrderId.required' => 'Не указан идентификатор заказа',
                'OrderId.numeric' => 'Неверно указан идентификатор заказа',
                'OrderItemIds.array' => 'Список идентификатора позиций в заказе на продление не соответсвует формату',
                'RetrieveMainServices.boolean' => 'Неверно указано значение "формировать бланки по апсейлам"',
                'RetrieveUpsales.boolean' => 'Неверно указан "формировать бланки по основным услугам"',
            ]
        );

        if ($validator->fails()) {

            return ResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);
        } else {

            $order = $this->model::where('orderId', $request->OrderId)->first();

            if (!$order) {
                return ResponseHelpers::jsonResponse([
                    'error' => 'ЖД заявка не найдена!'
                ], 404);
            }

            $orderData = StringHelpers::ObjectToArray($order->orderData);

            $response = OrderReservation::doBlank(
                [
                    "OrderId" => $orderData['orderId'],
                    "OrderItemId" => $request->input('OrderItemId'),
                    "OrderItemIds" => $request->input('OrderItemIds'),
                    "RetrieveMainServices" => $request->input('RetrieveMainServices'),
                    "RetrieveUpsales" => $request->input('RetrieveUpsales'),
                ]
            );

            Storage::put('blanks/railway/' . $orderData['orderId'] . '.pdf', $response);

            if (!$response) {
                return ResponseHelpers::jsonResponse([
                    'error' => OrderReservation::getLastError()
                ], 500);
            }

            return ResponseHelpers::jsonResponse($response);
        }
    }

    /**
     * Выполняем отмена бронирования
     * https://test.onelya.ru/ApiDocs/Api?apiId=Order-V1-Reservation-Cancel
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getCancel(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'OrderId' => 'required|numeric',
                'OrderItemIds' => 'array|nullable',
                'OrderCustomerIds' => 'array|nullable',
            ],
            [
                'OrderId.required' => 'Не указан идентификатор заказа',
                'OrderId.numeric' => 'Неверно указан идентификатор заказа',
                'OrderItemIds.array' => 'Список идентификатора позиций в заказе на продление не соответсвует формату',
                'OrderCustomerIds.array' => 'Список идентификаторов пользователей на отмену не соответсвует формату'
            ]
        );

        if ($validator->fails()) {
            return ResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);
        } else {

            $order = $this->model::where('orderId', $request->OrderId)->first();

            if ($order) {
                $orderData = StringHelpers::ObjectToArray($order->orderData);

                $response = OrderReservation::doCancel(
                    [
                        'OrderId' => $orderData['orderId'],
                        "OrderItemIds" => $request->input('OrderItemIds'),
                        "OrderCustomerIds" => $request->input('OrderCustomerIds'),
                    ]
                );

                if (!$response) {
                    return ResponseHelpers::jsonResponse([
                        'error' => OrderReservation::getLastError()
                    ], 500);
                }

                if (!$order->update(['orderStatus' => OrdersRailway::ORDER_STATUS_CANCELED])) {
                    return ResponseHelpers::jsonResponse([
                        'error' => 'Не удалось обновить статус'
                    ], 500);
                }
            } else {
                return ResponseHelpers::jsonResponse([
                    'result' => false, 'error' => 'Заказ с таким orderId не найден.'
                ], 404);
            }

            return ResponseHelpers::jsonResponse(['result' => true, 'OrderId' => $request->OrderId]);
        }
    }


    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return $this
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }
}
