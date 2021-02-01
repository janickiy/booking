<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelpers;
use App\Http\Controllers\Controller;
use App\Services\External\InnovateMobility\v1\AdditionalBaggage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Helpers\StringHelpers;
use App\Services\SessionLog;


/**
 * Class AdditionalBaggageController
 * @group Railway Additional Baggage
 * [Дополнительный багаж]
 * @package App\Http\Controllers\Api
 */
class AdditionalBaggageController extends Controller
{

    /**
     * Pricing
     * [Справка по перевозке багажа]
     *
     * https://test.onelya.ru/ApiDocs/Api?apiId=Railway-V1-AdditionalBaggage-Pricing
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getPricing(Request $request)
    {
        $Items = [];

        if (isset($request->Items) && is_array($request->Items)) {

            foreach ($request->Items as $item) {

                $validator = Validator::make(
                    $item, [
                    'MainServiceReference' => 'required',
                    'BaggageRequest' => 'required',
                    'Index' => 'required|numeric',
                ],
                    [
                        'MainServiceReference.required' => 'Не указана ссылка на основную услугу',
                        'BaggageRequest.required' => 'Не указано описание запрашиваемого перевоза багажа',
                        'Index.required' => 'Не указан номер позиции запроса',
                        'Index.numeric' => 'Неверно указан номер позиции запроса',
                    ]
                );

                if ($validator->fails()) {
                    return ResponseHelpers::jsonResponse([
                        'error' => $validator->messages()
                    ], 400);
                } else {
                    $baggage = AdditionalBaggage::BaggageRequest($item['BaggageRequest']['$type'], $item['BaggageRequest']);

                    $Items[] = [
                        'MainServiceReference' => $item['MainServiceReference'],
                        'BaggageRequest' => $baggage,
                        'Index' => $item['Index']
                    ];
                }
            }
        }

        $response = AdditionalBaggage::doPricing(['Items' => $Items]);

        if (!$response) {
            return ResponseHelpers::jsonResponse([
                'error' => AdditionalBaggage::getLastError()
            ], 500);
        }

        return ResponseHelpers::jsonResponse($response);

    }

    /**
     * Book
     * [Бронирование перевозки багажа]
     *
     * https://test.onelya.ru/ApiDocs/Api?apiId=Railway-V1-AdditionalBaggage-Book
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getBook(Request $request)
    {
        $Items = [];

        if (isset($request->Items) && is_array($request->Items)) {

            foreach ($request->Items as $item) {

                $validator = Validator::make(
                    $item, [
                    'MainServiceReference' => 'required',
                    'BaggageRequest' => 'required',
                    'ProviderPaymentForm' => 'in:' . implode(',', array_keys(trans('references/im.providerPaymentForm'))),
                    'Index' => 'required|numeric',
                ],
                    [
                        'MainServiceReference.required' => 'Не указана ссылка на основную услугу',
                        'ProviderPaymentForm.in' => 'Неверно указана форма оплаты',
                        'BaggageRequest.required' => 'Не указано описание запрашиваемого перевоза багажа',
                        'Index.required' => 'Не указан номер позиции запроса',
                        'Index.numeric' => 'Неверно указан номер позиции запроса',
                    ]
                );

                if ($validator->fails()) {
                    return ResponseHelpers::jsonResponse([
                        'error' => $validator->messages()
                    ], 400);
                }

                $baggage = AdditionalBaggage::BaggageRequest($item['BaggageRequest']['$type'], $item['BaggageRequest']);

                $Items[] = [
                    'MainServiceReference' => $item['MainServiceReference'],
                    'AgentPaymentId' => $item['AgentPaymentId'],
                    'AgentReferenceId' => $item['AgentReferenceId'],
                    'ProviderPaymentForm' => $item['ProviderPaymentForm'],
                    'BaggageRequest' => $baggage,
                    'Index' => $item['Index']
                ];

            }
        }

        $response = AdditionalBaggage::doBook(['Items' => $Items]);

        if (!$response) {
            return ResponseHelpers::jsonResponse([
                'error' => AdditionalBaggage::getLastError()
            ], 500);
        }

        return ResponseHelpers::jsonResponse($response);

    }

    /**
     * Cancel
     * [Отмена брони перевозки багажа]
     * https://test.onelya.ru/ApiDocs/Api?apiId=Railway-V1-AdditionalBaggage-Cancel
     * @param Request
     * @bodyParam orderId integer required Идентификатор заказа
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getCancel(Request $request)
    {

        $validator = Validator::make($request->all(),
            [
                'orderId' => 'required|numeric',
            ],
            [
                'orderId.required' => 'Не указан идентификатор заказа',
                'orderId.numeric' => 'Неверно указан идентификатор заказа',
            ]
        );

        if ($validator->fails()) {
            return ResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);
        }

        $user = $request->user('web');

        if (!$user) {

            SessionLog::orderLog($request->orderId,'cancel', 'Пользователь не авторизован', true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse(['error' => true, 'message' => 'Auth required'], 401);
        }

        $order = $user->orders()->getById($request->orderId)->first();

        if (!$order) {

            SessionLog::orderLog($request->orderId, 'cancel', 'Заказ с OrderId ' . $request->orderId . ' не найден', true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse(['order' => null], 404);
        }

        if ($order->id != $request->orderId) {
            $items = $order->getItemById($request->orderId);
            if (!$items) {

                SessionLog::orderLog($request->orderId, 'cancel', 'Заказ с OrderId ' . $request->orderId . ' не найден', true, StringHelpers::ObjectToArray($request));

                return ResponseHelpers::jsonResponse(['order' => null], 404);
            }
            $orderData = StringHelpers::ObjectToArray($items->orderData);
        } else {
            $items = $order->getItems();
            $orderData = StringHelpers::ObjectToArray($items[0]->orderData);
        }

        $response = AdditionalBaggage::doCancel(
            [
                'OrderItemId' => $orderData['result']['ReservationResults'][0]["OrderItemId"],
            ]
        );

        if (!$response) {

            SessionLog::orderLog($request->orderId, 'cancel', AdditionalBaggage::getLastError()->Message, true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse([
                'error' => AdditionalBaggage::getLastError()
            ], 500);
        }

        SessionLog::orderLog($request->orderId, 'cancel', 'Отмена брони перевозки багажа', false);

        return ResponseHelpers::jsonResponse(StringHelpers::convertToCamelCase(StringHelpers::ObjectToArray($response)));

    }

    /**
     * Confirm
     * [Подтверждение брони перевозки багажа]
     * https://test.onelya.ru/ApiDocs/Api?apiId=Railway-V1-AdditionalBaggage-Confirm
     * @bodyParam orderId integer required Идентификатор заказа
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getConfirm(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'orderId' => 'required|numeric',
            ],
            [
                'orderId.required' => 'Не указан идентификатор заказа',
                'orderId.numeric' => 'Неверно указан идентификатор заказа',
            ]
        );

        if ($validator->fails()) {
            return ResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);
        }

        $user = $request->user('web');

        if (!$user) {

            SessionLog::orderLog($request->orderId, 'confirm', 'Пользователь не авторизован', true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse(['error' => true, 'message' => 'Auth required'], 401);
        }

        $order = $user->orders()->getById($request->orderId)->first();

        if (!$order) {

            SessionLog::orderLog($request->orderId, 'confirm', 'Заказ с OrderId ' . $request->orderId . ' не найден', true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse(['order' => null], 404);
        }

        if ($order->id != $request->orderId) {
            $items = $order->getItemById($request->orderId);
            if (!$items) {

                SessionLog::orderLog($request->orderId, 'confirm', 'Заказ с OrderId ' . $request->orderId . ' не найден', true, StringHelpers::ObjectToArray($request));

                return ResponseHelpers::jsonResponse(['order' => null], 404);
            }
            $orderData = StringHelpers::ObjectToArray($items->orderData);
        } else {
            $items = $order->getItems();
            $orderData = StringHelpers::ObjectToArray($items[0]->orderData);
        }

        $response = AdditionalBaggage::doConfirm(
            [
                'OrderItemId' => $orderData['result']['ReservationResults'][0]["OrderItemId"],
            ]
        );

        if (!$response) {

            SessionLog::orderLog($request->orderId, 'confirm', AdditionalBaggage::getLastError()->Message, true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse([
                'error' => AdditionalBaggage::getLastError()
            ], 500);
        }

        SessionLog::orderLog($request->orderId, 'confirm', 'Подтверждение брони перевозки багажа', false);

        return ResponseHelpers::jsonResponse(StringHelpers::convertToCamelCase(StringHelpers::ObjectToArray($response)));

    }

    /**
     * Return
     * [Отмена оплаченной перевозки багажа]
     * https://test.onelya.ru/ApiDocs/Api?apiId=Railway-V1-AdditionalBaggage-Return
     * @bodyParam orderId integer required Идентификатор заказа
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getReturn(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'orderId' => 'required|numeric',
            ],
            [
                'orderId.required' => 'Не указан идентификатор заказа',
                'orderId.numeric' => 'Неверно указан идентификатор заказа',
            ]
        );

        if ($validator->fails()) {
            return ResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);
        }

        $user = $request->user('web');

        if (!$user) {

            SessionLog::orderLog($request->orderId, 'return', 'Пользователь не авторизован', true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse(['error' => true, 'message' => 'Auth required'], 401);
        }

        $order = $user->orders()->getById($request->orderId)->first();

        if (!$order) {

            SessionLog::orderLog($request->orderId, 'return', 'Заказ с OrderId ' . $request->orderId . ' не найден', true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse(['order' => null], 404);
        }

        if ($order->id != $request->orderId) {
            $items = $order->getItemById($request->orderId);
            if (!$items) {

                SessionLog::orderLog($request->orderId, 'return', 'Заказ с OrderId ' . $request->orderId . ' не найден', true, StringHelpers::ObjectToArray($request));

                return ResponseHelpers::jsonResponse(['order' => null], 404);
            }
            $orderData = StringHelpers::ObjectToArray($items->orderData);
        } else {
            $items = $order->getItems();
            $orderData = StringHelpers::ObjectToArray($items[0]->orderData);
        }

        $response = AdditionalBaggage::doReturn(
            [
                'OrderItemId' => $orderData['result']['ReservationResults'][0]["OrderItemId"],
                'AgentReferenceId' => null,
            ]
        );

        if (!$response) {

            SessionLog::orderLog($request->orderId, 'return', AdditionalBaggage::getLastError()->Message, true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse([
                'error' => AdditionalBaggage::getLastError()
            ], 500);
        }

        SessionLog::orderLog($request->orderId, 'return', 'Отмена оплаченной перевозки багажа', false);

        return ResponseHelpers::jsonResponse(StringHelpers::convertToCamelCase(StringHelpers::ObjectToArray($response)));

    }
}