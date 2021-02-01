<?php

namespace App\Services\External\Payture;

use App\Helpers\ResponseHelpers;
use App\Services\External\Payture\Models\{PayInfoRequest, ChequeRequest};

class PaytureAPI
{

    /**
     * [Запрос Pay используется для быстрого проведения клиентского платежа одним действием.]
     * https://payture.com/api#payture-api_pay_
     * @param $orderId
     * @param $amount
     * @param $payInfo
     * @param string $paytureId
     * @param string $customerKey
     * @param array $customFields
     * @param string $cheque
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public static function Pay($orderId, $amount, $payInfo, $paytureId = "", $customerKey = "", $customFields = array(), $cheque = "")
    {
        $payInfoRequest = new PayInfoRequest($payInfo);

        if ($cheque) {
            $chequeInfo = (new ChequeRequest($cheque))->getBodyToJSONBase64();
        } else {
            $chequeInfo = null;
        }

        $response = Payments::doPay([
                'Key' => config('trivago.services.payture.key'), // Идентификатор ТСП. Выдается с параметрами тестового/боевого доступа
                'PayInfo' => $payInfoRequest->getBodyToUrlEncoded(), // Параметры для совершения транзакции
                'OrderId' => $orderId, // Идентификатор платежа в системе ТСП
                'Amount' => $amount, // Сумма платежа в копейках
                'PaytureId' => $paytureId, //  Идентификатор платежа в системе Payture AntiFraud
                'CustomerKey' => $customerKey, // Идентификатор Покупателя в системе Payture AntiFraud
                'CustomFields' => $customFields, // пользовательские поля
                'Cheque' => $chequeInfo // Информация о чеке
            ]
        );

        if (!$response) {
            return ['error' => Payments::getError()];
        }

        return $response;
    }

    /**
     * [Этот запрос позволяет блокировать денежные средства на карте Покупателя для последующего списания. ]
     * https://payture.com/api#payture-api_block_
     * @param $orderId
     * @param $amount
     * @param $payInfo
     * @param string $paytureId
     * @param string $customerKey
     * @param array $customFields
     * @param string $cheque
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public static function Block($orderId, $amount, $payInfo, $paytureId = "", $customerKey = "", $customFields = array(), $cheque = "")
    {
        $payInfoRequest = new PayInfoRequest($payInfo);

        if ($cheque) {
            $chequeRequest = (new ChequeRequest($cheque))->getBodyToJSONBase64();
        } else {
            $chequeRequest = null;
        }

        $response = Payments::doBlock([
                'Key' => config('trivago.services.payture.key'), // Идентификатор ТСП. Выдается с параметрами тестового/боевого доступа
                'PayInfo' => $payInfoRequest->getBodyToUrlEncoded(), // Параметры для совершения транзакции
                'OrderId' => $orderId, // Идентификатор платежа в системе ТСП
                'Amount' => $amount, // Сумма платежа в копейках
                'PaytureId' => $paytureId, // Идентификатор платежа в системе Payture AntiFraud
                'CustomerKey' => $customerKey, // Идентификатор Покупателя в системе Payture AntiFraud
                'CustomFields' => $customFields, // Дополнительные поля транзакции.
                'Cheque' => $chequeRequest // Информация о чеке в формате JSON, закодированная в Base64
            ]
        );

        if (!$response) {
            return ['error' => Payments::getError()];
        }

        return $response;
    }

    /**
     * [Запрос используется для списания денежных средств с карты покупателя, предварительно заблокированных командой Block.]
     * https://payture.com/api#payture-api_charge_
     * @param $orderId
     * @return \stdClass
     */
    public static function Charge($orderId, $cheque = "")
    {
        if ($cheque) {
            $chequeRequest = (new ChequeRequest($cheque))->getBodyToJSONBase64();
        } else {
            $chequeRequest = null;
        }

        $response = Payments::doCharge([
                'Key' => config('trivago.services.payture.key'), // Идентификатор ТСП. Выдается с параметрами тестового/боевого доступа
                'OrderId' => $orderId, // Идентификатор платежа в системе ТСП
                'Cheque' => $chequeRequest // Информация о чеке в формате JSON, закодированная в Base64
            ]
        );

        if (!$response) {
            return ['error' => Payments::getError()];
        }

        return $response;
    }

    /**
     * @param $orderId
     * @param $paRes
     * @return array
     */
    public static function Block3DS($orderId, $paRes)
    {
        $response = Payments::doBlock3DS([
                'Key' => config('trivago.services.payture.key'), // Идентификатор ТСП. Выдается с параметрами тестового/боевого доступа
                'OrderId' => $orderId, // Идентификатор платежа в системе ТСП
                'PaRes' => $paRes // Шифрованная строка, содержащая результаты 3-D Secure аутентификации
            ]
        );

        if (!$response) {
            return ['error' => Payments::getError()];
        }

        return $response;
    }

    /**
     * [Запрос позволяет полностью снять блокирование денежных средств, предварительно заблокированных командой Block.]
     * https://payture.com/api#payture-api_unblock_
     * @param $orderId
     * @param $amount
     * @return \stdClass
     */
    public static function Unblock($orderId, $amount)
    {
        $response = Payments::doUnblock([
                'Key' => config('trivago.services.payture.key'), // Идентификатор ТСП. Выдается с параметрами тестового/боевого доступа
                'OrderId' => $orderId, // Идентификатор платежа в системе ТСП
                'Amount' => $amount, // Сумма разблокировки в копейках, должна быть равна заблокированной сумме
            ]
        );

        if (!$response) {
            return ['error' => Payments::getError()];
        }

        return $response;
    }

    /**
     * [Этот запрос используется для возврата денежных средств, списанных командой Pay или Charge, на карту покупателя. ]
     * https://payture.com/api#payture-api_refund_
     * @param $orderId
     * @param $amount
     * @return \stdClass
     */
    public static function Refund($orderId, $amount, $cheque = "")
    {
        if ($cheque) {
            $chequeRequest = (new ChequeRequest($cheque))->getBodyToJSONBase64();
        } else {
            $chequeRequest = null;
        }

        $response = Payments::doRefund([
                'Key' => config('trivago.services.payture.key'), // Идентификатор ТСП. Выдается с параметрами тестового/боевого доступа
                'Password' => config('trivago.services.payture.password'), // Пароль ТСП для проведения операций через API.
                'OrderId' => $orderId, // Идентификатор платежа в системе ТСП
                'Amount' => $amount, // Сумма, которую следует вернуть, в копейках
                'Cheque' => $chequeRequest, // Информация о чеке в формате JSON, закодированная в Base64
            ]
        );

        if (!$response) {
            return ['error' => Payments::getError()];
        }

        return $response;
    }

    /**
     * [Запрос используется для получения информации о текущем состоянии платежа. ]
     * https://payture.com/api#payture-api_getstate_
     * @param $orderId
     * @return \stdClass
     */
    public static function GetState($orderId)
    {
        $response = Payments::doGetState([
                'Key' => config('trivago.services.payture.key'), // Идентификатор ТСП. Выдается с параметрами тестового/боевого доступа
                'OrderId' => $orderId, // Идентификатор платежа в системе ТСП
            ]
        );

        if (!$response) {
            return ['error' => Payments::getError()];
        }

        return $response;
    }
}