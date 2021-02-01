<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Services\External\InnovateMobility\v1\OrderInfo;
use App\Helpers\ResponseHelpers;
use App\Http\Controllers\Controller;
use App\Helpers\StringHelpers;
use App\Models\OrdersRailway;
use Illuminate\Support\Facades\Validator;
use App\Services\Settings;

/**
 * Class OrderInfoController
 * [Методы для получения информации по заказам]
 * @group Orders info
 *  * @package App\Http\Controllers\Api
 */
class OrderInfoController extends Controller
{
    /**
     * OrderOldInfo
     * [Получение информации о заказе]
     * https://test.onelya.ru/ApiDocs/Api?apiId=Order-V1-Info-OrderInfo
     *
     * @bodyParam OrderId integer required Идентификатор заказа
     * @bodyParam AgentReferenceId string Внешний (агентский) идентификатор для позиции
     * @param Request $request
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getOrderOldInfo(Request $request)
    {
        $order = OrdersRailway::where('orderId', $request->OrderId)->first();
        $orderData = StringHelpers::ObjectToArray($order->orderData);

        if (!$order) {
            return ResponseHelpers::jsonResponse([
                'error' => 'ЖД заказ не найден!'
            ], 404);
        }

        $response = OrderInfo::doOrderInfo([
            'OrderId' => $orderData['orderId'],
            'AgentReferenceId' => $request->AgentReferenceId
        ]);

        if (!$response) {
            return [
                'error' => OrderInfo::getLastError(),
                'code' => 500
            ];
        }

        return ResponseHelpers::jsonResponse($response);
    }

    /**
     * OrderList
     * [Получение информации о заказах за период]
     * https://test.onelya.ru/ApiDocs/Api?apiId=Order-V1-Info-OrderList
     *
     * @bodyParam Date datetime required Дата, за которую необходимо получить информацию
     * @bodyParam OperationType string required Тип операции
     * @bodyParam ProviderPaymentForm string ProviderPaymentForm
     * @bodyParam IsExternallyLoaded IsExternallyLoaded boolean Признак загрузки из сторонней системы (например, возвраты, проведенные в кассах РЖД).
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getOldOrderList(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'Date' => 'required|date_format:Y-m-d\TH:i:s',
                'OperationType' => 'required',
            ],
            [
                'Date.date_format' => 'Неверно указана дата',
                'Date.required' => 'Не указана дата',
                'ProviderPaymentForm.required' => 'Не указана форма оплаты у поставщика',
            ]);

        if ($validator->fails()) {
            return ResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);
        } else {
            $response = OrderInfo::doOrderList([
                'Date' => $request->Date,
                'OperationType' => $request->OperationType,
                'ProviderPaymentForm' => $request->ProviderPaymentForm,
                'IsExternallyLoaded' => $request->IsExternallyLoaded
            ]);

            if (!$response) {
                return [
                    'error' => OrderInfo::getLastError(),
                    'code' => 500
                ];
            }

            return ResponseHelpers::jsonResponse($response);
        }
    }

    /**
     * Get orders list
     * [Получение списка заказов]
     * @queryParam page Номер страницы, по умолчанию выводится 1-я страница, выводится 20 заказов на страницу
     * @bodyParam filters array Набор фильтров для отбора заказов, пример {"type":["railway","aeroexpress"],"status":[1,2],"user":"keyword","dates":["2019-03-06","2019-03-07"],"passenger":"иванов"}
     * @bodyParam order array Сортировка по, по умолчанию ["created_at","desc"]
     *
     * @response {
     * "orders":[
     * {
     * "id":1,
     * "items":[]
     * }
     * ],
     * "pagesCount": 1
     * }
     * @response 404 {
     * "orders": null
     * }
     * @response 401 {
     * "error": true,
     * "message": "Auth required"
     * }
     * @param Request $request
     * @param integer $page
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getOrderList(Request $request, $page = 1)
    {

        $limit = 20;
        $offset = $limit * ($page - 1);

        $user = $request->user('web');

        if (!$user) return ResponseHelpers::jsonResponse(['error' => true, 'message' => 'Auth required'], 401);

        $ordersRequest = $user->orders()->byServices($user->services());
        $ordersCount = $ordersRequest->count();
        $pagesCount = ceil($ordersCount / $limit);

        if($request->has('filters')){
            $ordersRequest = $ordersRequest->byFilters($request->get('filters', []));
        }

        if($request->has('order')){
            $orderField = $request->get('order')[0] ?? 'created_at';
            $orderDir  = $request->get('order')[1] ?? 'asc';
            $ordersRequest = $ordersRequest->orderBy($orderField,$orderDir);
        }else{
            $ordersRequest = $ordersRequest->orderBy('created_at', 'desc');
        }

        $orders = $ordersRequest->limit($limit)->offset($offset)->get();

        if ($orders->count() < 1) return ResponseHelpers::jsonResponse(['orders' => null], 200);

        return ResponseHelpers::jsonResponse(['orders' => $orders, 'pagesCount' => $pagesCount], 200);

    }

    /**
     * Get order info
     * [Получение данных заказа]
     * @queryParam orderId Id заказа
     * @response {
     *  "type": "railway",
     *  "order": {
     *  "userId": 24,
     *  "holdingId": 0,
     *  "complexOrderId": 281,
     *  "orderStatus": 2,
     *  "orderData": {}
     *  "provider": "IM",
     *  "orderId": 282,
     *  "ContactPhone": "+79013452311",
     *  "ContactEmails": "alexander.yanitsky@trivago.ru"
     * }
     * }
     * @response {
     *  "type": "complex",
     *  "order": {
     * "id":1,
     * "items":[]
     * }
     * }
     * @response 404 {
     * "order": null
     * }
     * @response 401 {
     * "error": true,
     * "message": "Auth required"
     * }
     * @param Request $request
     * @param $orderId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getOrderInfo(Request $request, $orderId)
    {
        $user = $request->user('web');

        if (!$user) return ResponseHelpers::jsonResponse(['error' => true, 'message' => 'Auth required'], 401);

        $order = $user->orders()->getById($orderId)->first();
        if (!$order) return ResponseHelpers::jsonResponse(['order' => null], 404);

        $returnOrder = $order->toArray();
        $returnOrder['type'] = $type = 'complex';

        $settings = new Settings();
        $rzhd = (float) $settings->get('taxRailwayImRzdPurchase');
        $agent = (float) $settings->get('taxRailwayImTrivagoPurchase');
        $total = $rzhd + $agent;

        if ($order->id != $orderId) {
            $returnOrder = $order->getItemById($orderId);

            if (!$returnOrder) return ResponseHelpers::jsonResponse(['order' => null], 404);

            $row = $this->getFormatList($returnOrder, ['rzhd' => $rzhd, 'agent' => $agent, 'total' => $total]);
            $type = $returnOrder->type;
        } else {
            foreach ($order->getItems() as $item) {
                $row[] = $this->getFormatList($item, ['rzhd' => $rzhd, 'agent' => $agent, 'total' => $total]);
            }
        }

        return ResponseHelpers::jsonResponse(['type' => $type, 'order' => $row], 200);

    }

    /**
     * @param $item
     * @return array
     */
    private function getFormatList($item, $options = [])
    {
        if (isset($options['rzhd'])) {
            $rzhd = $options['rzhd'];
        } else {
            $rzhd = 0;
        }

        if (isset($options['agent'])) {
            $agent = $options['agent'];
        } else {
            $agent = 0;
        }

        if (isset($options['total'])) {
            $total = $options['total'];
        } else {
            $total = 0;
        }

        $result = [
            "userId" => $item->userId,
            "holdingId" => $item->holdingId,
            "complexOrderId" => $item->complexOrderId,
            "orderStatus" => $item->orderStatus,
            "orderData" => $item->orderData,
            "provider" => $item->provider,
            "orderId" => $item->orderId,
            "ContactPhone" => $item->orderData->ContactPhone,
            "ContactEmails" => $item->orderData->ContactEmails,
            "orderDocuments" => $item->orderDocuments,
        ];

        $result = StringHelpers::ObjectToArray($result);

        if (isset($result['orderData']['Customers'])) unset($result['orderData']['Customers']);
        if (isset($result['orderData']['ContactPhone'])) unset($result['orderData']['ContactPhone']);
        if (isset($result['orderData']['ContactEmails'])) unset($result['orderData']['ContactEmails']);
        if (isset($result['orderData']['OrderId'])) unset($result['orderData']['OrderId']);
        if (isset($result['orderData']['result']['OrderId'])) unset($result['orderData']['result']['OrderId']);
        if (isset($result['orderData']['result']['ReservationResults'])) unset($result['orderData']['result']['OrderId']);

        if (isset($result['orderDocuments'])) {
            $result['orderData'] = array_merge($result['orderData'],['OrderItemBlanks' => $result['orderDocuments']]);

            unset($result['orderDocuments']);
        }

        $customers = [];

        if (isset($result['orderData']['result']['Customers'])) {
            foreach ($result['orderData']['result']['Customers'] as $customer) {
                $customers[$customer["Index"]] = $customer;
            }

            unset($result['orderData']['result']['Customers']);
        }

        if (isset($result['orderData']['result']['ReservationResults'])) {
            $reservationResults = [];

            foreach ($result['orderData']['result']['ReservationResults'] as $row) {
                if (isset($row['AgentReferenceId'])) unset($row['AgentReferenceId']);
                if (isset($row['Fare'])) unset($row['Fare']);
                if (isset($row['Tax'])) unset($row['Tax']);
                if (isset($row['AgentFeeCalculation'])) unset($row['AgentFeeCalculation']);
                if (isset($row['ClientFeeCalculation'])) unset($row['ClientFeeCalculation']);

                $blanks = [];

                if (isset($row["Blanks"])) {

                    foreach ($row["Blanks"] as $blank) {
                        $blanks[$blank["OrderItemBlankId"]] = $blank;
                    }

                    unset($row["Blanks"]);
                }

                if (isset($row["Passengers"])) {
                    $passengers = [];

                    foreach ($row["Passengers"] as $passenger) {
                        $passengers[] = isset($customers[$passenger['OrderCustomerReferenceIndex']]) ? array_merge($passenger, $customers[$passenger['OrderCustomerReferenceIndex']], ['blank' => isset($blanks[$passenger["OrderItemBlankId"]]) ? $blanks[$passenger["OrderItemBlankId"]] : null]) : array_merge($passenger, ['blank' => isset($blanks[$passenger["OrderItemBlankId"]]) ? $blanks[$passenger["OrderItemBlankId"]] : null]);
                    }

                    $row["Passengers"] = $passengers;
                }

                $reservationResults[] = $row;
            }

            $result['orderData']['result']['ReservationResults'] = $reservationResults;
        }

        $result['orderData'] = array_merge($result['orderData'], $result['orderData']['result']);
        $result['orderData']["Amount"] = $item->Amount;

        $result['orderData']["tax"] = [
            'rzhd' => $rzhd,
            'agent' => $agent,
            'total' => $total,
        ];

        unset($result['orderData']['result']);

        if (isset($result['orderData']["orderId"])) unset($result['orderData']["orderId"]);

        return $result;
    }
}