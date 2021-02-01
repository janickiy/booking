<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ResponseHelpers;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Services\External\InnovateMobility\v1\Card;
use App\Services\External\InnovateMobility\Models\RailwayCardPassengerRequest;

/**
 * Class CardController
 * [Оформление ЖД карт]
 * @group Railway Card
 * @package App\Http\Controllers\Api
 */
class CardController extends Controller
{

    /**
     * Pricing
     * [Запрос информации по вариантам и ценам доступных к оформлению ЖД карт]
     *
     * https://test.onelya.ru/ApiDocs/Api?apiId=Railway-V1-Card-Pricing
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getPricing()
    {
        $response = Card::doPricing();

        if (!$response) {
            return ResponseHelpers::jsonResponse([
                'error' => Card::getLastError()
            ], 500);
        }

        return ResponseHelpers::jsonResponse($response);
    }

    /**
     * Checkout
     * [Cоздание операции для дальнейшей покупки дополнительного питания для перевозки.]
     *
     * https://test.onelya.ru/ApiDocs/Api?apiId=Railway-V1-AdditionalMeal-Checkout
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getCheckout(Request $request)
    {
        $passenger = new RailwayCardPassengerRequest($request->Passenger);

        if (!$passenger->validate()) {
            return ResponseHelpers::jsonResponse([
                'error' => [$passenger->getValidationErrors()]
            ], 400);
        }

        $validator = Validator::make($request->all(),
            [
                'StartDateOfAction' => 'date_format:Y-m-d\TH:i:s',
                'ProviderPaymentForm' => 'in:' . implode(',', array_keys(trans('references/im.providerPaymentForm'))),
            ],
            [
                'StartDateOfAction.date_format' => 'Неверно указан дата начала действия карты',
                'ProviderPaymentForm.in' => 'Неверно указана форма оплаты'
            ]);

        if ($validator->fails()) {
            return ResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);
        }

        $response = Card::doCheckout(
            [
                'StartDateOfAction' => $request->StartDateOfAction,
                'TariffCode' => $request->TariffCode,
                'Passenger' => $passenger->getBody(),
                'AgentPaymentId' => $request->AgentPaymentId,
                'AgentReferenceId' => $request->AgentReferenceI,
                'ProviderPaymentForm' => $request->ProviderPaymentForm
            ]
        );

        if (!$response) {
            return ResponseHelpers::jsonResponse([
                'error' => Card::getLastError()
            ], 500);
        }
    }

    /**
     * Purchase
     * [Покупка ЖД карты]
     *
     * https://test.onelya.ru/ApiDocs/Api?apiId=Railway-V1-Card-Purchase
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getPurchase(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'OrderItemId' => 'required|numeric',
            ],
            [
                'OrderItemId.required' => 'Не указан идентификатор позиции',
                'OrderItemId.numeric' => 'Неверно указан идентификатор позиции'
            ]
        );

        if ($validator->fails()) {
            return ResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);
        }

        $response = Card::doPurchase(
            [
                'OrderItemId' => $request->OrderItemId,
            ]
        );

        if (!$response) {
            return ResponseHelpers::jsonResponse([
                'error' => Card::getLastError()
            ], 500);
        }

        return ResponseHelpers::jsonResponse($response);
    }

    /**
     * Cancel
     * [Отмена создания операции для ЖД карты]
     *
     * https://test.onelya.ru/ApiDocs/Api?apiId=Railway-V1-Card-Cancel
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getCancel(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'OrderItemId' => 'required|numeric',
            ],
            [
                'OrderItemId.required' => 'Не указан идентификатор позиции',
                'OrderItemId.numeric' => 'Неверно указан идентификатор позиции'
            ]
        );

        if ($validator->fails()) {
            return ResponseHelpers::jsonResponse([
                'error' => $validator->messages()
            ], 400);
        }

        $response = Card::doCancel(
            [
                'OrderItemId' => $request->OrderItemId,
            ]
        );

        if (!$response) {
            return ResponseHelpers::jsonResponse([
                'error' => Card::getLastError()
            ], 500);
        }

        return ResponseHelpers::jsonResponse($response);
    }
}