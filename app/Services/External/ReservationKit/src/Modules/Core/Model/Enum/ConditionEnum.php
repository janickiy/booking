<?php

namespace ReservationKit\src\Modules\Core\Model\Enum;

use ReservationKit\src\Component\Type\Enum;

class ConditionEnum extends Enum
{
    /**
     * Все
     */
    const ALL = 'all';

    /**
     * Только ...
     */
    const ONLY = 'only';

    /**
     * Ничего
     */
    const NONE = 'none';

    /**
     * Содержит ...
     */
    const CONTAINS = 'contains';

    /**
     * Не содержит ...
     */
    const NOT_CONTAINS = 'not_contains';

    /**
     * Соответствует
     */
    const EQUALS = 'equals';
}