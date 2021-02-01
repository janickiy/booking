<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 17.01.2019
 * Time: 13:56
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class OrdersPayment extends Model
{
    const ORDER_PROVIDER_PAYTURE = 'payture';
    const ORDER_PROVIDER_RSB = 'rsb';
    const ORDER_PROVIDER_1C = '1c';
    const ORDER_PROVIDER_TRIVAGO = 'trivago';

    const STATUS_CREATED = 0;
    const STATUS_FINISHED = 1;
    const STATUS_REVERSED = -1;
    const STATUS_ERROR = -2;

    protected $table = 'orders_payment';
    protected $primaryKey = 'id';

    protected $fillable = [
        'userId',
        'clientId',
        'holdingId',
        'payItems',
        'transactionId',
        'complexOrderId',
        'provider',
        'type',
        'request',
        'response',
        'amount',
    ];

    public static $type_name = [
        self::STATUS_CREATED => 'создан',
        self::STATUS_FINISHED => 'выполнен',
        self::STATUS_REVERSED => 'зарезервирован',
        self::STATUS_ERROR => 'ошибка',
    ];


    /**
     * @param $value
     */
    public function setPayItemsAttribute($value)
    {
        $this->attributes['payItems'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getPayItemsAttribute($value)
    {
        return json_decode($value);
    }

    /**
     * @return mixed
     */
    public function getTypeNameAttribute()
    {
        return self::$type_name[$this->status];
    }

    /**
     * @param $value
     */
    public function setRequestAttribute($value)
    {
        $this->attributes['request'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getRequestAttribute($value)
    {
        return json_decode($value);
    }

    /**
     * @param $value
     */
    public function setResponseAttribute($value)
    {
        $this->attributes['response'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getResponseAttribute($value)
    {
        return json_decode($value);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function order()
    {
        return $this->hasOne(Orders::class,'id','complexOrderId');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user()
    {
        return $this->hasOne(User::class, 'userId', 'userId');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function client()
    {
        return $this->hasOne(User::class, 'userId', 'clientId');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function holding()
    {
        return $this->hasOne(User::class, 'userId', 'holdingId');
    }

    /**
     * @param Builder $query
     * @param $id
     * @return mixed
     */
    public function scopeGetById(Builder $query, $id)
    {
        return $query->whereRaw('"payItems" @> \'[{"id":'.$id.'}]\'::jsonb');
    }
}