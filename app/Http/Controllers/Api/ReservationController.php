<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelpers;
use App\Helpers\StringHelpers;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Services\External\InnovateMobility\v1\Reservation;
use App\Services\SessionLog;

/**
 * Class ReservationController
 * [Дополнительные методы по работе с ЖД-билетами в заказе]
 * @group Railway Reservation Additional
 * APIs for IM Reservation orders
 */
class ReservationController extends Controller
{

    /**
     * Update Blanks
     * [Получение и обновление информации о бланках от поставщика]
     * https://test.onelya.ru/ApiDocs/Api?apiId=Railway-V1-Reservation-UpdateBlanks
     * @bodyParam orderId integer required Идентификатор заказа
     * @param Request $request
     * @response {
     * "blanks": [],
     * "IsModified": false
     * }
     * @response 404 {
     * "orders": null
     * }
     * @response 401 {
     * "error": true,
     * "message": "Auth required"
     * }
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getUpdateBlanks(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'orderId' => 'required|numeric',
            ],
            [
                'orderId.required' => 'Не указан идентификатор позиции',
                'orderId.numeric' => 'Неверно указан идентификатор позиции'
            ]
        );

        if ($validator->fails()) {
            return ResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);
        }

        $user = $request->user('web');

        if (!$user) {

            SessionLog::orderLog($request->orderId, 'updateblanks', 'Пользователь не авторизован', true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse(['error' => true, 'message' => 'Auth required'], 401);
        }

        $order = $user->orders()->getById($request->orderId)->first();

        if (!$order) {

            SessionLog::orderLog($request->orderId, 'updateblanks', 'Заказ с OrderId ' . $request->orderId . ' не найден', true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse(['order' => null], 404);
        }

        if ($order->id != $request->orderId) {
            $items = $order->getItemById($request->orderId);
            if (!$items) {

                SessionLog::orderLog($request->orderId, 'updateblanks', 'Заказ с OrderId ' . $request->orderId . ' не найден', true, StringHelpers::ObjectToArray($request));

                return ResponseHelpers::jsonResponse(['order' => null], 404);
            }
            $orderData = StringHelpers::ObjectToArray($items->orderData);
        } else {
            $items = $order->getItems();
            $orderData = StringHelpers::ObjectToArray($items[0]->orderData);
        }

        $response = Reservation::doUpdateBlanks(
            [
                'OrderItemId' => $orderData['result']['ReservationResults'][0]["OrderItemId"],
            ]
        );

        if (!$response) {

            SessionLog::orderLog($request->orderId, 'updateblanks', Reservation::getLastError()->Message, true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse([
                'error' => Reservation::getLastError()
            ], 500);
        }

        SessionLog::orderLog($request->orderId,'updateblanks', 'Получение и обновление информации о бланках от поставщика', false);

        return ResponseHelpers::jsonResponse(StringHelpers::convertToCamelCase(StringHelpers::ObjectToArray($response)));

    }

    /**
     * Electronic Registration
     * [Установка/отмена электронной регистрации]
     * https://test.onelya.ru/ApiDocs/Api?apiId=Railway-V1-Reservation-ElectronicRegistration
     * @bodyParam orderId integer required Идентификатор заказа
     * @bodyParam orderItemBlankIds array Идентификаторы бланков, которые требуется отменить.
     * @bodyParam set boolean required Установить электронную регистрацию: true - установить, false - отменить
     * @bodyParam sendNotification boolean Выслылать или нет нотификацию клиенту
     * @param Request $request
     * @response {
     * "ExpirationElectronicRegistrationDateTime": "2019-04-28T12:40:00",
     * "blanks": []
     * }
     * @response 404 {
     * "orders": null
     * }
     * @response 401 {
     * "error": true,
     * "message": "Auth required"
     * }
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getElectronicRegistration(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'orderId' => 'required|numeric',
                'orderItemBlankIds' => 'array|nullable',
                'set' => 'required|boolean',
                'sendNotification' => 'boolean|nullable',
            ],
            [
                'orderId.required' => 'Не указан идентификатор позиции',
                'orderId.numeric' => 'Неверно указан идентификатор позиции',
                'orderItemBlankIds.array' => 'Неверно указаны идентификаторы бланков',
                'set.required' => 'Не указан параметр set',
                'set.boolean' => 'Неверно указан параметр set',
                'sendNotification.boolean' => 'Не указан параметр SendNotification',
            ]
        );

        if ($validator->fails()) {
            return ResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);
        }

        $user = $request->user('web');

        if (!$user) {

            SessionLog::orderLog($request->orderId, 'electronicregistration', 'Пользователь не авторизован', true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse(['error' => true, 'message' => 'Auth required'], 401);
        }

        $order = $user->orders()->getById($request->orderId)->first();

        if (!$order) {

            SessionLog::orderLog($request->orderId, 'electronicregistration', 'Заказ с orderId ' . $request->orderId . ' не найден', true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse(['order' => null], 404);
        }

        if ($order->id != $request->orderId) {
            $items = $order->getItemById($request->orderId);
            if (!$items) {

                SessionLog::orderLog($request->orderId,'electronicregistration', 'Заказ с orderId ' . $request->orderId . ' не найден', true, StringHelpers::ObjectToArray($request));

                return ResponseHelpers::jsonResponse(['order' => null], 404);
            }
            $orderData = StringHelpers::ObjectToArray($items->orderData);
        } else {
            $items = $order->getItems();
            $orderData = StringHelpers::ObjectToArray($items[0]->orderData);
        }

        $response = Reservation::doElectronicRegistration(
            [
                'OrderItemId' => $orderData['result']['ReservationResults'][0]["OrderItemId"],
                'OrderItemBlankIds' => $request->orderItemBlankIds,
                'Set' => $request->set,
                'SendNotification' => $request->sendNotification,
            ]
        );

        if (!$response) {

            SessionLog::orderLog($request->orderId, 'electronicregistration', Reservation::getLastError()->Message, true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse([
                'error' => Reservation::getLastError()
            ], 500);
        }

        SessionLog::orderLog($request->orderId, 'electronicregistration', 'Установка/отмена электронной регистрации', false);

        return ResponseHelpers::jsonResponse(StringHelpers::convertToCamelCase(StringHelpers::ObjectToArray($response)));

    }

    /**
     * Meal Option
     * [Смена выбранного рациона питания]
     * https://test.onelya.ru/ApiDocs/Api?apiId=Railway-V1-Reservation-MealOption
     * @bodyParam orderId integer required Идентификатор заказа
     * @bodyParam mealOptionCode string required Код рациона питания
     * @bodyParam orderItemBlankId string required Идентификатор бланка, для которого нужно сменить питание
     * @response {
     * "MealOptionCode": "Б"
     * }
     * @response 404 {
     * "orders": null
     * }
     * @response 401 {
     * "error": true,
     * "message": "Auth required"
     * }
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getMealOption(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'orderId' => 'required|numeric',
                'mealOptionCode' => 'required',
                'orderItemBlankId' => 'required|numeric'
            ],
            [
                'orderId.required' => 'Не указан идентификатор заказа',
                'orderId.numeric' => 'Неверно указан идентификатор заказа',
                'mealOptionCode.required' => 'Не указан код рациона питания',
                'orderItemBlankId.required' => 'Не указан идентификатор бланка',
                'orderItemBlankId.numeric' => 'Неверно указан идентификатор бланка',
            ]
        );

        if ($validator->fails()) {
            return ResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);
        }

        $user = $request->user('web');

        if (!$user) {

            SessionLog::orderLog($request->orderId, 'mealoption', 'Пользоватль не авторизован', true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse(['error' => true, 'message' => 'Auth required'], 401);
        }

        $order = $user->orders()->getById($request->orderId)->first();

        if (!$order) {
            SessionLog::orderLog($request->orderId, 'mealoption', 'Заказ с orderId ' . $request->orderId . ' не найден', true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse(['order' => null], 404);
        }

        if ($order->id != $request->orderId) {
            $items = $order->getItemById($request->orderId);
            if (!$items) {

                SessionLog::orderLog($request->orderId, 'mealoption', 'Заказ с orderId ' . $request->orderId . ' не найден', true, StringHelpers::ObjectToArray($request));

                return ResponseHelpers::jsonResponse(['order' => null], 404);
            }
            $orderData = StringHelpers::ObjectToArray($items->orderData);
        } else {
            $items = $order->getItems();
            $orderData = StringHelpers::ObjectToArray($items[0]->orderData);
        }

        $response = Reservation::doMealOption(
            [
                'OrderItemId' => $orderData['result']['ReservationResults'][0]["OrderItemId"],
                'MealOptionCode' => $request->mealOptionCode,
                'OrderItemBlankId' => $request->orderItemBlankId
            ]
        );

        if (!$response) {

            SessionLog::orderLog($request->orderId, 'mealoption', Reservation::getLastError()->Message, true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse([
                'error' => Reservation::getLastError()
            ], 500);
        }

        SessionLog::orderLog($request->orderId, 'mealoption', 'Смена выбранного рациона питания', false);

        return ResponseHelpers::jsonResponse(StringHelpers::convertToCamelCase(StringHelpers::ObjectToArray($response)));

    }

    /**
     * Blank As Html
     * [Получение маршрут-квитанции в формате HTML]
     * https://test.onelya.ru/ApiDocs/Api?apiId=Railway-V1-Reservation-BlankAsHtml
     * @bodyParam orderId integer required Идентификатор позиции в заказе
     * @param Request $request
     * @response HTML
     * @response 404 {
     * "orders": null
     * }
     * @response 401 {
     * "error": true,
     * "message": "Auth required"
     * }
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getBlankAsHtml(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'orderId' => 'required|numeric',
            ],
            [
                'orderId.required' => 'Не указан идентификатор идентификатор заказа',
                'orderId.numeric' => 'Неверно указан идентификатор идентификатор заказа',
            ]
        );

        if ($validator->fails()) {
            return ResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);
        }

        $user = $request->user('web');

        if (!$user) {

            SessionLog::orderLog($request->orderId, 'blankashtml', 'Пользователь не авторизован', true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse(['error' => true, 'message' => 'Auth required'], 401);
        }

        $order = $user->orders()->getById($request->orderId)->first();

        if (!$order) {

            SessionLog::orderLog($request->orderId, 'blankashtml', 'Заказ с orderId ' . $request->orderId . ' не найден', true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse(['order' => null], 404);
        }

        if ($order->id != $request->orderId) {
            $items = $order->getItemById($request->orderId);
            if (!$items) {

                SessionLog::orderLog($request->orderId, 'blankashtml', 'Заказ с orderId ' . $request->orderId . ' не найден', true, StringHelpers::ObjectToArray($request));

                return ResponseHelpers::jsonResponse(['order' => null], 404);
            }
            $orderData = StringHelpers::ObjectToArray($items->orderData);
        } else {
            $items = $order->getItems();
            $orderData = StringHelpers::ObjectToArray($items[0]->orderData);
        }

        $orderItemId = [];

        foreach ($orderData['result']['ReservationResults'] as $item) {
            $orderItemId[] = $item['OrderItemId'];
        }

        $response = Reservation::doBlankAsHtml(
            [
                'OrderItemId' => 0,
                'OrderItemIds' => $orderItemId,
            ]
        );

        if (!$response) {

            SessionLog::orderLog($request->orderId, 'blankashtml', Reservation::getLastError()->Message, true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse([
                'error' => Reservation::getLastError()
            ], 500);
        }

        SessionLog::orderLog($request->orderId, 'blankashtml', 'Получение маршрут-квитанции в формате HTML', false);

        return $response;
    }

    /**
     * Check Transit Permission Approval
     * [Проверка возможности транзитного проезда]
     * https://test.onelya.ru/ApiDocs/Api?apiId=Railway-V1-Reservation-CheckTransitPermissionApproval
     * @bodyParam orderId integer required Идентификатор заказа
     * @param Request $request
     * @response {
     * "blanks": []
     * }
     * @response 404 {
     * "orders": null
     * }
     * @response 401 {
     * "error": true,
     * "message": "Auth required"
     * }
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getCheckTransitPermissionApproval(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'orderId' => 'required|numeric',
            ],
            [
                'orderId.required' => 'Не указан идентификатор идентификатор заказа',
                'orderId.numeric' => 'Неверно указан идентификатор идентификатор заказа'
            ]
        );

        if ($validator->fails()) {
            return ResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);
        }

        $user = $request->user('web');

        if (!$user) {

            SessionLog::orderLog($request->orderId, 'checktransitpermissionapproval', 'Пользователь не авторизован', true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse(['error' => true, 'message' => 'Auth required'], 401);
        }

        $order = $user->orders()->getById($request->orderId)->first();

        if (!$order) {

            SessionLog::orderLog($request->orderId, 'checktransitpermissionapproval','Заказ с orderId ' . $request->orderId . ' не найден', true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse(['order' => null], 404);
        }

        if ($order->id != $request->orderId) {
            $items = $order->getItemById($request->orderId);
            if (!$items) {

                SessionLog::orderLog($request->orderId, 'checktransitpermissionapproval','Заказ с orderId ' . $request->orderId . ' не найден', true, StringHelpers::ObjectToArray($request));

                return ResponseHelpers::jsonResponse(['order' => null], 404);
            }
            $orderData = StringHelpers::ObjectToArray($items->orderData);
        } else {
            $items = $order->getItems();
            $orderData = StringHelpers::ObjectToArray($items[0]->orderData);
        }

        $response = Reservation::doCheckTransitPermissionApproval(
            [
                'OrderItemId' => $orderData['result']['ReservationResults'][0]["OrderItemId"],
            ]
        );

        if (!$response) {

            SessionLog::orderLog($request->orderId, 'checktransitpermissionapproval',Reservation::getLastError()->Message, true, StringHelpers::ObjectToArray($request));

            return ResponseHelpers::jsonResponse([
                'error' => Reservation::getLastError()
            ], 500);
        }

        SessionLog::orderLog($request->orderId, 'checktransitpermissionapproval','Проверка возможности транзитного проезда', false);

        return ResponseHelpers::jsonResponse(StringHelpers::convertToCamelCase(StringHelpers::ObjectToArray($response)));

    }
}