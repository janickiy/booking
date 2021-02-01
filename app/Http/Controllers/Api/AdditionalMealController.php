<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelpers;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Services\External\InnovateMobility\v1\AdditionalMeal;
use App\Services\External\InnovateMobility\Models\{RailwayMainServiceOrderItemReference,
    RailwayMainServiceOrderItemAndBlankReferenceRequest};
use App\Helpers\StringHelpers;
use App\Services\SessionLog;

/**
 * Class AdditionalMealController
 * [Дополнительное питание (за отдельную плату)]
 * @group Railway  Additional Meal
 * @package App\Http\Controllers\Api
 */
class AdditionalMealController extends Controller
{

    /**
     * Pricing
     * [Запрос информации по дополнительному питанию по указанной перевозке]
     *
     * https://test.onelya.ru/ApiDocs/Api?apiId=Railway-V1-AdditionalMeal-Pricing
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getPricing(Request $request)
    {
        $service = new RailwayMainServiceOrderItemReference($request->MainServiceReference);

        if (!$service->validate()) {
            return [
                'error' => [$service->getValidationErrors()],
                'code' => 400
            ];
        }

        $response = AdditionalMeal::doPricing(['MainServiceReference' => $service->getBody()]);

        if (!$response) {
            return ResponseHelpers::jsonResponse([
                'error' => AdditionalMeal::getLastError()
            ], 500);
        }

        return ResponseHelpers::jsonResponse($response);

    }

    /**
     * Checkout
     * [Создание операции для дальнейшей покупки дополнительного питания]
     *
     * https://test.onelya.ru/ApiDocs/Api?apiId=Railway-V1-AdditionalMeal-Checkout
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getCheckout(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'ProviderPaymentForm' => 'in:' . implode(',', array_keys(trans('references/im.providerPaymentForm'))),
                'MealTimes' => 'required|array',
            ],
            [
                'MealTimes.required' => 'Не указан список всех выбранных вариантов питания',
                'MealTimes.array' => 'Неверно указан формат даты списока всех выбранных вариантов питания',
            ]
        );

        if ($validator->fails()) {
            return ResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);
        }

        $service = new RailwayMainServiceOrderItemAndBlankReferenceRequest($request->MainServiceReference);

        if (!$service->validate()) {

            return ResponseHelpers::jsonResponse([
                'error' => [$service->getValidationErrors()]
            ], 400);
        }

        $response = AdditionalMeal::doCheckout(
            [
                'MainServiceReference' => $service->getBody(),
                'AgentPaymentId' => $request->AgentPaymentId,
                'AgentReferenceId' => $request->AgentReferenceId,
                'ProviderPaymentForm' => $request->ProviderPaymentForm,
                'MealTimes' => $request->MealTimes
            ]
        );

        if (!$response) {
            return ResponseHelpers::jsonResponse([
                'error' => AdditionalMeal::getLastError()
            ], 500);
        }

        return ResponseHelpers::jsonResponse($response);

    }

    /**
     * Cancel
     * [Отмена создания операции]
     * https://test.onelya.ru/ApiDocs/Api?apiId=Railway-V1-AdditionalMeal-Cancel
     * @param Request $request
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
                'orderId.required' => 'Не указан идентификатор позиции',
                'orderId.numeric' => 'Неверно указан идентификатор позиции',
            ]
        );

        if ($validator->fails()) {
            return ResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);
        }

        $user = $request->user('web');

        if (!$user) {

            SessionLog::orderLog($request->orderId, 'cancel', 'Пользователь не авторизован', true, StringHelpers::ObjectToArray($request));

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

        $response = AdditionalMeal::doCancel(
            [
                'OrderItemId' => $orderData['result']['ReservationResults'][0]["OrderItemId"],
            ]
        );

        if (!$response) {

            SessionLog::orderLog($request->orderId, 'cancel', AdditionalMeal::getLastError()->Message, true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse([
                'error' => AdditionalMeal::getLastError()
            ], 500);
        }

        SessionLog::orderLog($request->orderId, 'cancel', 'Отмена создания операции', false);

        return ResponseHelpers::jsonResponse(StringHelpers::convertToCamelCase(StringHelpers::ObjectToArray($response)));

    }

    /**
     * Purchase
     * [Покупка дополнительного питания]
     * https://test.onelya.ru/ApiDocs/Api?apiId=Railway-V1-AdditionalMeal-Purchase
     * @param Request $request
     * @bodyParam orderId integer required Идентификатор заказа
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getPurchase(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'orderId' => 'required|numeric',
            ],
            [
                'orderId.required' => 'Не указан идентификатор позиции',
                'orderId.numeric' => 'Неверно указан идентификатор позиции',
            ]
        );

        if ($validator->fails()) {
            return ResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);
        }

        $user = $request->user('web');

        if (!$user) {

            SessionLog::orderLog($request->orderId, 'purchase', 'Пользователь не авторизован', true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse(['error' => true, 'message' => 'Auth required'], 401);
        }

        $order = $user->orders()->getById($request->orderId)->first();

        if (!$order) {

            SessionLog::orderLog($request->orderId, 'purchase', 'Заказ с OrderId ' . $request->orderId . ' не найден', true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse(['order' => null], 404);
        }

        if ($order->id != $request->orderId) {
            $items = $order->getItemById($request->orderId);
            if (!$items) {

                SessionLog::orderLog($request->orderId, 'purchase', 'Заказ с OrderId ' . $request->orderId . ' не найден', true, StringHelpers::ObjectToArray($request));

                return ResponseHelpers::jsonResponse(['order' => null], 404);
            }
            $orderData = StringHelpers::ObjectToArray($items->orderData);
        } else {
            $items = $order->getItems();
            $orderData = StringHelpers::ObjectToArray($items[0]->orderData);
        }

        $response = AdditionalMeal::doPurchase(
            [
                'OrderItemId' => $orderData['result']['ReservationResults'][0]["OrderItemId"],
            ]
        );

        if (!$response) {

            SessionLog::orderLog($request->orderId, 'purchase', AdditionalMeal::getLastError()->Message, true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse([
                'error' => AdditionalMeal::getLastError()
            ], 500);
        }

        SessionLog::orderLog($request->orderId, 'purchase', 'Покупка дополнительного питания', false);

        return ResponseHelpers::jsonResponse(StringHelpers::convertToCamelCase(StringHelpers::ObjectToArray($response)));

    }

    /**
     * Return
     * [Возврат дополнительного питания]
     * https://test.onelya.ru/ApiDocs/Api?apiId=Railway-V1-AdditionalMeal-Return
     * @param Request $request
     * @bodyParam orderId integer required Идентификатор заказа
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getReturn(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'OrderItemId' => 'required|numeric',
            ],
            [
                'OrderItemId.required' => 'Не указан идентификатор позиции',
                'OrderItemId.numeric' => 'Неверно указан идентификатор позиции',
            ]
        );

        if ($validator->fails()) {
            return ResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);
        }

        $user = $request->user('web');

        if (!$user) return ResponseHelpers::jsonResponse(['error' => true, 'message' => 'Auth required'], 401);

        $order = $user->orders()->getById($request->orderId)->first();

        if (!$order) return ResponseHelpers::jsonResponse(['order' => null], 404);

        if ($order->id != $request->orderId) {
            $items = $order->getItemById($request->orderId);
            if (!$items) return ResponseHelpers::jsonResponse(['order' => null], 404);
            $orderData = StringHelpers::ObjectToArray($items->orderData);
        } else {
            $items = $order->getItems();
            $orderData = StringHelpers::ObjectToArray($items[0]->orderData);
        }

        $response = AdditionalMeal::doReturn(
            [
                'OrderItemId' => $orderData['result']['ReservationResults'][0]["OrderItemId"],
                'AgentReferenceId' => null
            ]
        );

        if (!$response) {
            return ResponseHelpers::jsonResponse([
                'error' => AdditionalMeal::getLastError()
            ], 500);
        }

        return ResponseHelpers::jsonResponse(StringHelpers::convertToCamelCase(StringHelpers::ObjectToArray($response)));
    }

}