<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Orders extends Model
{
    const ORDER_TYPE_RAILWAY = 'railway';
    const ORDER_TYPE_BUS = 'bus';
    const ORDER_TYPE_AVIA = 'avia';
    const ORDER_TYPE_AEROEXPRESS = 'aeroexpress';
    const ORDER_TYPE_INSURANCE = 'insurance';

    protected $appends = ['items'];
    protected $table = 'orders';
    protected $primaryKey = 'id';

    protected $fillable = [
        'orderItems',
        'userId',
        'holdingId',
    ];

    protected $visible = [
        'id',
        'items',
        'created_at'
    ];

    public static $type_name = [
        self::ORDER_TYPE_RAILWAY => 'ЖД',
        self::ORDER_TYPE_BUS => 'Автобусы',
        self::ORDER_TYPE_AVIA => 'Авиа',
        self::ORDER_TYPE_AEROEXPRESS => 'Аэроэкспресс',
        self::ORDER_TYPE_INSURANCE => 'Страхование НС',
    ];

    private static $type_models = [
        self::ORDER_TYPE_RAILWAY => OrdersRailway::class,
        self::ORDER_TYPE_BUS => false,
        self::ORDER_TYPE_AVIA => OrdersAvia::class,
        self::ORDER_TYPE_AEROEXPRESS => OrdersAeroexpress::class,
        self::ORDER_TYPE_INSURANCE => false,
    ];

    /**
     * @param $value
     */
    public function setOrderItemsAttribute($value)
    {
        $this->attributes['orderItems'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getOrderItemsAttribute()
    {
        return json_decode($this->attributes['orderItems']);
    }

    /**
     * @return array
     */
    public function getItemsAttribute()
    {
        $items = [];
        foreach ($this->orderItems as $item) {
            if (isset(static::$type_models[$item->type]) && static::$type_models[$item->type]) {
                $itemModel = static::$type_models[$item->type];
                $itemResult = $itemModel::find($item->id);
                if ($itemResult) {
                    if (method_exists($itemModel, 'formatForList')) {
                        $items[] = $itemResult->formatForList();
                    } else {
                        $items[] = $itemResult;
                    }
                }
            }
        }
        return $items;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        $items = [];
        foreach ($this->orderItems as $item) {
            if (static::$type_models[$item->type]) {
                $itemModel = static::$type_models[$item->type];
                $itemResult = $itemModel::find($item->id);
                if ($itemResult) {
                    $items[] = $itemResult;
                }
            }
        }
        return $items;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'userId', 'userId');
    }

    public function railway()
    {
        return $this->hasMany(OrdersRailway::class, 'complexOrderId', 'id');
    }

    public function aeroexpress()
    {
        return $this->hasMany(OrdersAeroexpress::class, 'complexOrderId', 'id');
    }

    /**
     * @param Builder $query
     * @param $id
     * @return Builder|\Illuminate\Database\Query\Builder
     */
    public function scopeGetById(Builder $query, $id)
    {
        return $query->where('id', $id)
            ->orWhereRaw('"orderItems" @> \'[{"id":' . $id . '}]\'::jsonb');
    }

    public function scopeByServices(Builder $query, array $services)
    {
        $query->where(function($q)use($services){
            foreach ($services as $service){
                $q->orWhereRaw('"orderItems" @> \'[{"type":"' . $service . '"}]\'::jsonb');
            }
        });

        return $query;
    }

    /**
     * @param $id
     * @return null
     */
    public function getItemById($id)
    {
        $requestedItem = null;

        foreach ($this->orderItems as $item) {
            if ($item->id == $id) {
                if (self::$type_models[$item->type]) {
                    $requestedItem = self::$type_models[$item->type]::find($id);
                }
            }
        };

        return $requestedItem;
    }

    /**
     * @param Builder $query
     * @param $filters
     * @return Builder
     */
    public function scopeByFilters(Builder $query, $filters)
    {
        foreach ($filters as $name => $params) {
            switch ($name) {
                case 'type':
                    $query = $query->where(function ($uq) use ($params) {
                        foreach ($params as $relatedOrderType) {
                            if (method_exists(self::class, $relatedOrderType)) {
                                $uq = $uq->orHas($relatedOrderType);
                            }
                        }
                    });
                    break;

                case 'status':
                    $query = $query->where(function ($uq) use ($params) {
                        foreach (array_keys(self::$type_name) as $orderType) {
                            if (method_exists(self::class, $orderType)) {
                                $uq = $uq->orWhereHas($orderType, function ($q) use ($params) {
                                    $q->whereIn('orderStatus', $params);
                                });
                            }
                        }
                    });
                    break;

                case 'user':
                    $query = $query->where(function ($uq) use ($params) {
                        $uq = $uq->whereHas('user', function ($q) use ($params) {
                            $q->where('email', 'ilike', $params . '%')
                                ->orWhere('contacts->firstName', 'ilike', "{$params}%")
                                ->orWhere('contacts->lastName', 'ilike', "{$params}%")
                                ->orWhere('contacts->contactEmails', 'ilike', "{$params}%");
                        });
                    });
                    break;
                case 'dates':
                    $query = $query->where(function ($uq) use ($params) {
                        foreach (array_keys(self::$type_name) as $orderType) {
                            if (method_exists(self::class, $orderType)) {
                                $uq = $uq->orWhereHas($orderType, function ($q) use ($params) {
                                    $q->whereBetween('created_at', $params);
                                });
                            }
                        }
                    });
                    break;

                case 'passenger':
                    $query = $query->where(function ($uq) use ($params) {
                        foreach (array_keys(self::$type_name) as $orderType) {
                            if (method_exists(self::class, $orderType)) {
                                $uq = $uq->orWhereHas($orderType, function ($q) use ($params, $orderType) {
                                    $keyword = mb_convert_case($params, MB_CASE_TITLE);
                                    switch ($orderType) {
                                        case 'railway':
                                        case 'aeroexpress':
                                            $q->whereRaw('"passengersData" @> \'[{"FirstName":"' . $keyword . '"}]\'::jsonb')
                                                ->orWhereRaw('"passengersData" @> \'[{"LastName":"' . $keyword . '"}]\'::jsonb');
                                            break;
                                    }
                                });
                            }
                        }
                    });
                    break;
            }
        }
        //dd($query->toSql());
        return $query;
    }
}
