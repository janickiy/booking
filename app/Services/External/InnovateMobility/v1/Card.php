<?php

namespace App\Services\External\InnovateMobility\v1;

use App\Services\External\InnovateMobility\Request;

/**
 * Class Card
 * @package App\Services\External\InnovateMobility\v1
 *
 * @method static getPricing(array $options = [], boolean $map = false, array $mapOptions = []) Получение информации о наличии и стоимости ЖД карт
 * @method static getCheckout(array $options = [], boolean $map = false, array $mapOptions = []) Проверка данных и создание операции для дальнейшей покупки ЖД карты
 * @method static getPurchase(array $options = [], boolean $map = false, array $mapOptions = []) Покупка ЖД карты
 * @method static getCancel(array $options = [], boolean $map = false, array $mapOptions = []) Отмена создания операции для ЖД карты
 */
class Card extends Request
{
    /**
     * {@inheritDoc}
     */
    protected static $basePath = 'Railway/V1/Card/';

    /**
     * {@inheritDoc}
     */
    protected static $methods = [
        'Pricing', // Получение информации о наличии и стоимости ЖД карт
        'Checkout', // Проверка данных и создание операции для дальнейшей покупки ЖД карты
        'Purchase', // Покупка ЖД карты
        'Cancel', // Отмена создания операции для ЖД карты
    ];
}