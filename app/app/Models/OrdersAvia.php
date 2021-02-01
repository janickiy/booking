<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdersAvia extends Model
{
    protected $table = 'orders_avia';

    protected $primaryKey = 'id';

    const ORDER_STATUS_CREATED   = 0;
    const ORDER_STATUS_RESERVED  = 1;
    const ORDER_STATUS_COMPLETED = 2;
    const ORDER_STATUS_CANCELED  = -1;

    public static $status_name = [
        self::ORDER_STATUS_CREATED   => 'создан',
        self::ORDER_STATUS_RESERVED  => 'зарезервирован',
        self::ORDER_STATUS_COMPLETED => 'оплачен',
        self::ORDER_STATUS_CANCELED  => 'отменен'
    ];

    protected $fillable = [
        'id',
        'status',
        'user_id',
        'booking_data'
    ];

    protected $visible = [
        'id',
        'status',
        'user_id',
        'booking_data',
        'created_at'
    ];

    public function getTypeAttribute()
    {
        return 'avia';
    }

    /**
     * @param $value
     */
    public function setBookingDataAttribute($value)
    {
        // TODO перевести объект бронировани я в json
        //$this->attributes['booking_data'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $this->attributes['booking_data'] = serialize($value);
    }

    /**
     * @return mixed
     */
    public function getBookingDataAttribute()
    {
        // TODO перевести объект бронирования из json
        //return json_decode($this->attributes['booking_data']);
        return unserialize($this->attributes['booking_data']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user()
    {
        return $this->hasOne(User::class,'userId', 'user_id');
    }

    public function formatForList()
    {
        $results = [];
        $user = $this->user;

        return [
            'id'         => $this->order_id,
            'type'       => Orders::$type_name['avia'],
            'status'     => static::$status_name[$this->status],
            'results'    => $results,
            'user'       => null,   //$user ? $user->contacts : null,
            'payTill'    => null,     //$this->orderStatus > 1 ? null : $this->payTill,
            'amount'     => '',     // $this->Amount,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s')
        ];
    }
}