<?php

namespace App\Services\External\InnovateMobility\v1;

use App\Services\External\InnovateMobility\Request;


/**
 * Class Reservation
 * @package App\Services\External\InnovateMobility\v1
 *
 * @method static getCreate(array $options = [], boolean $map = false, array $mapOptions = []) //  Создание бронирования
 * @method static getProlongReservation(array $options = [], boolean $map = false, array $mapOptions = []) // Продление бронирования до трех часов
 * @method static getConfirm(array $options = [], boolean $map = false, array $mapOptions = []) // Подтверждение бронирования
 * @method static getBlank(array $options = [], boolean $map = false, array $mapOptions = []) // Получение маршрут-квитанции
 * @method static getCancel(array $options = [], boolean $map = false, array $mapOptions = []) // Отмена бронирования
 * @method static getReturnAmount(array $options = [], boolean $map = false, array $mapOptions = []) // Получение суммы планируемого автоматического возврата
 * @method static doAutoReturn(array $options = [], boolean $map = false, array $mapOptions = []) // Проведение автоматического возврата
 * @method static doVoid(array $options = [], boolean $map = false, array $mapOptions = []) // Аннулирование подтвержденного бронирования
 * @method static getAddUpsale(array $options = [], boolean $map = false, array $mapOptions = []) // Добавление апсейла (доп. сервиса) к основной услуге
 * @method static getRefuseUpsale(array $options = [], boolean $map = false, array $mapOptions = []) // Отказ от апсейла (доп. сервиса)
 * @method static getCreateExchange(array $options = [], boolean $map = false, array $mapOptions = []) // Обмен. Бронирование новых билетов в обмен на старые
 * @method static getConfirmExchange(array $options = [], boolean $map = false, array $mapOptions = []) // Подтверждение обмена. Подтверждение бронирование новых билетов и возврат старых, соответствующих обмену
 *
 */
class OrderReservation extends Request
{
    /**
     * {@inheritDoc}
     */
    protected static $basePath = 'Order/V1/Reservation/';

    /**
     * {@inheritDoc}
     */
    protected static $methods = [
        'Create', // Создание бронирования
        'ProlongReservation', // Продление бронирования до трех часов
        'Confirm', // Подтверждение бронирования
        'Blank', // Получение маршрут-квитанции
        'Cancel', // Отмена бронирования
        'ReturnAmount', // Получение суммы планируемого автоматического возврата
        'AutoReturn', // Проведение автоматического возврата
        'Void', // Аннулирование подтвержденного бронирования.
        'AddUpsale', // Добавление апсейла (доп. сервиса) к основной услуге
        'RefuseUpsale', // Отказ от апсейла (доп. сервиса)
        'CreateExchange', // Обмен. Бронирование новых билетов в обмен на старые
        'ConfirmExchange', // Подтверждение обмена. Подтверждение бронирование новых билетов и возврат старых, соответствующих обмену
    ];

    /**
     * @param $data
     * @return mixed
     */
    protected static function mapCreate($data, $options = [])
    {
        if (isset($options['totalTax'])) {
            $totalTax = $options['totalTax'];
        } else {
            $totalTax = 0;
        }

        $totalAmount = 0.00;

        if (isset($data->ReservationResults)) {
            foreach ($data->ReservationResults as $reservationResult) {
                foreach ($reservationResult->Blanks as $blank) {
                    $blank->imAmount = $blank->Amount;
                    $blank->Amount = (float)$blank->Amount + $totalTax;
                    $totalAmount += $blank->Amount;
                }

                foreach ($reservationResult->Passengers as $passenger) {
                    if (isset($passenger->Amount)) {
                        $passenger->imAmount = $passenger->Amount;
                        $passenger->Amount = (float)$passenger->Amount + $totalTax;
                    }
                }

                $reservationResult->imAmount = $reservationResult->Amount;
                $reservationResult->Amount = $totalAmount;
            }
        }

        $data->totalAmount = $totalAmount;
        $data->imAmmount = $data->Amount;

        return $data;
    }
}