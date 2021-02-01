<?php

namespace App\Services\External\InnovateMobility\v1;

use App\Services\External\InnovateMobility\Request;

/**
 * Class Reservation
 * @package App\Services\External\InnovateMobility\v1
 *
 * @method static getUpdateBlanks(array $options = [], boolean $map = false, array $mapOptions = []) Получение и обновление информации о бланках от поставщика
 * @method static getElectronicRegistration(array $options = [], boolean $map = false, array $mapOptions = []) Установка/отмена электронной регистрации
 * @method static getMealOption(array $options = [], boolean $map = false, array $mapOptions = []) Смена выбранного рациона питания
 * @method static getBlankAsHtml(array $options = [], boolean $map = false, array $mapOptions = []) Получение маршрут-квитанции в формате HTML
 * @method static getCheckTransitPermissionApproval(array $options = [], boolean $map = false, array $mapOptions = []) Проверка возможности транзитного проезда
 */
class Reservation extends Request
{
    /**
     * {@inheritDoc}
     */
    protected static $basePath = 'Railway/V1/Reservation/';

    /**
     * {@inheritDoc}
     */
    protected static $methods = [
        'UpdateBlanks', // Получение и обновление информации о бланках от поставщика
        'ElectronicRegistration', // Установка/отмена электронной регистрации
        'MealOption', // Смена выбранного рациона питания
        'BlankAsHtml', // Получение маршрут-квитанции в формате HTML
        'CheckTransitPermissionApproval', // Проверка возможности транзитного проезда
    ];
}