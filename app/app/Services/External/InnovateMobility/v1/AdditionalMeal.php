<?php

namespace App\Services\External\InnovateMobility\v1;

use App\Services\External\InnovateMobility\{Request};


/**
 * Class AdditionalMeal
 * @package App\Services\External\InnovateMobility\v1
 *
 * @method static getPricing(array $options = [], boolean $map = false, array $mapOptions = []) Информация по дополнительному питанию
 * @method static getCheckout(array $options = [], boolean $map = false, array $mapOptions = []) Создание операции для дальнейшей покупки дополнительного питания
 * @method static getCancel(array $options = [], boolean $map = false, array $mapOptions = []) Отмена создания операции
 * @method static getPurchase(array $options = [], boolean $map = false, array $mapOptions = []) Покупка дополнительного питания
 * @method static getReturn(array $options = [], boolean $map = false, array $mapOptions = []) Возврат дополнительного питания
 */
class AdditionalMeal extends Request
{
    /**
     * {@inheritDoc}
     */
    protected static $basePath = 'Railway/V1/AdditionalMeal/';

    /**
     * {@inheritDoc}
     */
    protected static $methods = [
        'Pricing',
        // Запрос информации по дополнительному питанию по указанной перевозке. Для перевозки должна быть доступна данная услуга. Перевозка должна быть подтверждена
        'Checkout',
        // Cоздание операции для дальнейшей покупки дополнительного питания для перевозки. Перевозка должна быть забронирована или подтверждена
        'Cancel',
        // Отмена дополнительного питания (операции Checkout). Операция не может быть выполнена для купленного питания (то есть после успешной операции Purchase)
        'Purchase',
        // Покупка дополнительного питания для перевозки. Перевозка должна быть подтверждена
        'Return',
        // Возврат дополнительного питания
    ];

}