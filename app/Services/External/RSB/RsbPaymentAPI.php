<?php

namespace App\Services\External\RSB;

class RsbPaymentAPI
{
    const CURRENCY_RUB = 643;

    /**
     * [Регистрирует транзакцию в платежной системе]
     * @param $amount
     * @param $description
     * @return array
     */
    public static function TransactionDMS($amount, $description = '', $mrch_transaction_id = '')
    {
        $response = Payments::doTransactionDMS([
                'server_version' => '2.0',
                'command' => 'a',
                'amount' => $amount,
                'currency' => self::CURRENCY_RUB,
                'client_ip_addr' => request()->ip(),
                'description' => urlencode($description),
                'mrch_transaction_id' => $mrch_transaction_id,
                'language' => config('app.locale'),
            ]
        );

        if (!$response) {
            return [
                'error' => Payments::getError(),
                'code' => 500
            ];
        }

        return $response;
    }


    /**
     * [Возвращает статус транзакции по ее идентификатору]
     * @param $transID
     * @return array
     */
    public static function Status($transID)
    {
        $response = Payments::doStatus([
                'server_version' => '2.0',
                'client_ip_addr' => request()->ip(),
                'command' => 'c',
                'trans_id' => $transID,
            ]
        );

        if (!$response) {
            return [
                'error' => Payments::getError(),
                'code' => 500
            ];
        }

        return $response;
    }

    /**
     * [Завершает бизнес-день и возвращает данные]
     * @return array
     */
    public static function closeDay()
    {
        $response = Payments::doStatus([
                'server_version' => '2.0',
                'command' => 'b',
            ]
        );

        if (!$response) {
            return [
                'error' => Payments::getError(),
                'code' => 500
            ];
        }

        return $response;
    }

    /**
     * [Отменяет транзацию по ее идентификатору]
     * @param $transID
     * @param null $amount
     * @param bool $suspectedFraud
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public static function reverseTransaction($transID, $amount = null, $suspectedFraud = false)
    {
        $params = [
            'server_version' => '2.0',
            'command' => 'r',
            'trans_id' => $transID,
        ];

        if ($suspectedFraud) {
            $params['suspected_fraud'] = 1;
        } else if ($amount !== null) {
            $params['amount'] = $amount;
        }

        $response = Payments::doReverseTransaction($params);

        if (!$response) {
            return [
                'error' => Payments::getError(),
                'code' => 500
            ];
        }

        return $response;
    }

    /**
     * [Регистрирует транзакцию в платежной системе]
     * @param $transID
     * @param $amount
     * @param string $description
     * @return array
     */
    public static function ReservationDMS($transID, $amount, $description = '')
    {
        $params = [
            'trans_id' => $transID,
            'amount' => $amount * 100,
            'currency' => self::CURRENCY_RUB,
            'client_ip_addr' => request()->ip(),
            'description' => $description,
            'language' => config('app.locale'),
            'command' => 't',
            'msg_type' => 'DMS'
        ];

        $response = Payments::doReservationDMS($params);

        if (!$response) {
            return [
                'error' => Payments::getError(),
                'code' => 500
            ];
        }

        return $response;
    }

    /**
     * [Возврат денег]
     * @param $transID
     * @param null $amount
     * @return array
     */
    public static function Refund($transID, $amount = null)
    {
        $params = [
            'trans_id' => $transID,
            'command' => 'k',
        ];

        if ($amount !== null) {
            $params['amount'] = $amount;
        }

        $response = Payments::doReturn($params);

        if (!$response) {
            return [
                'error' => Payments::getError(),
                'code' => 500
            ];
        }

        return $response;
    }

    /**
     * [Регистрирует SMS транзакцию для оплаты на стороне организации]
     * https://business.rsb.ru/upload/iblock/f5a/instruktsiya_po_integratsii_rsb_ecomm_3_2.5.pdf
     * @param $amount Сумма  транзакции  в  целых  единицах, последние два символа –копейки
     * @param $email email клиента
     * @param $cardname Имя/фамилия держателя карты.
     * @param $pan Номер карты
     * @param $expiry Срок действия карты (в формате ГГММ).
     * @param $cvc2 CVC2/CVV2 значение
     * @param string $description Описание платежа
     * @return array
     */
    public static function CardSMSTransaction($amount, $email, $cardname, $pan, $expiry, $cvc2, $description = '')
    {
        $params = [
            'command' => 'i',
            'amount' => $amount,
            'currency' => self::CURRENCY_RUB,
            'client_ip_addr' => request()->ip(),
            'email_client' => $email,
            'description' => $description,
            'cardname' => $cardname,
            'pan' => $pan,
            'expiry' => $expiry,
            'cvc2' => $cvc2,
            'language' => config('app.locale'),
            'msg_type' => 'SMS',
        ];

        $response = Payments::doCardSMSTransaction($params);

        if (!$response) {
            return [
                'error' => Payments::getError(),
                'code' => 500
            ];
        }

        return $response;
    }

    /**
     * [DMS авторизация для оплаты на стороне организации]
     * @param $amount Сумма  транзакции  в  целых  единицах, последние два символа –копейки
     * @param $email email клиента
     * @param $cardname Имя/фамилия держателя карты.
     * @param $pan Номер карты
     * @param $expiry Срок действия карты (в формате ГГММ).
     * @param $cvc2 CVC2/CVV2 значение
     * @param string $description Описание платежа
     * @return array
     */
    public static function CardDMSAuth($amount, $email, $cardname, $pan, $expiry, $cvc2, $description = '')
    {
        $params = [
            'command' => 'j',
            'amount' => $amount,
            'currency' => self::CURRENCY_RUB,
            'client_ip_addr' => request()->ip(),
            'email_client' => $email,
            'description' => urlencode($description),
            'cardname' => $cardname,
            'pan' => $pan,
            'expiry' => $expiry,
            'cvc2' => $cvc2,
            'language' => config('app.locale'),
            'msg_type' => 'DMS'
        ];

        $response = Payments::doCardDMSAuth($params);

        if (!$response) {
            return [
                'error' => Payments::getError(),
                'code' => 500
            ];
        }

        return $response;
    }

    /**
     * [DMS транзакция/выполнение]
     * @param $transID
     * @param $amount
     * @param $description
     * @return array
     */
    public static function MakeDMSTransaction($transID, $amount, $description)
    {
        $params = [
            'command' => 't',
            'trans_id' => $transID,
            'amount' => $amount,
            'currency' => self::CURRENCY_RUB,
            'client_ip_addr' => request()->ip(),
            'description' => urlencode($description),
            'language' => config('app.locale'),
            'msg_type' => 'DMS'
        ];

        $response = Payments::doMakeDMSTransaction($params);

        if (!$response) {
            return [
                'error' => Payments::getError(),
                'code' => 500
            ];
        }

        return $response;
    }

}