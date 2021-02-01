<?php

namespace App\Services\External\RSB;

class Payments extends Request
{

    /**
     * {@inheritDoc}
     */
    protected static $methods = [
        'TransactionSMS', // Регистрирует SMS транзакцию в платежной системе
        'TransactionDMS', // Регистрирует SMS транзакцию в платежной системе
        'ReservationDMS', // Выполнение/Расчет DMS транзакции
        'CardSMSTransaction', // Регистрирует SMS транзакцию для оплаты на стороне организации
        'CardDMSAuth', // DMS авторизация для оплаты на стороне организации
        'MakeDMSTransaction', // DMS транзакция/выполнение
        'Refund', // Возврат денег
        'Status', // Возвращает статус транзакции по ее идентификатору
        'CloseDay', // Завершает бизнес-день и возвращает данные
        'ReverseTransaction', // Отменяет транзацию по ее идентификатору
    ];
}



