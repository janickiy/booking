<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdersRailway extends Model
{
    const ORDER_STATUS_CREATED = 0;
    const ORDER_STATUS_RESERVED = 1;
    const ORDER_STATUS_COMPLETED = 2;
    const ORDER_STATUS_CANCELED = -1;

    protected $table = 'orders_railway';
    protected $primaryKey = 'orderId';
    protected $appends = ['passengersData','orderData','orderDocuments'];
   // public $incrementing = false;

    public static $status_name = [
        self::ORDER_STATUS_CREATED => 'создан',
        self::ORDER_STATUS_RESERVED => 'зарезервирован',
        self::ORDER_STATUS_COMPLETED => 'оплачен',
        self::ORDER_STATUS_CANCELED => 'отменен'
        ];

    protected $fillable = [
        'orderId',
        'provider',
        'userId',
        'holdingId',
        'complexOrderId',
        'orderStatus',
        'passengersData',
        'orderData',
        'orderDocuments',
        'Amount'
    ];

    protected $visible = [
        'orderId',
        'provider',
        'userId',
        'holdingId',
        'complexOrderId',
        'orderStatus',
        'passengersData',
        'orderData',
        'orderDocuments',
        'created_at'
    ];

    public function getTypeAttribute()
    {
        return 'railway';
    }

    /**
     * @param $value
     */
    public function setPassengersDataAttribute($value)
    {
        $this->attributes['passengersData'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getPassengersDataAttribute()
    {
        return json_decode($this->attributes['passengersData']);
    }


    /**
     * @param $value
     */
    public function setOrderDataAttribute($value)
    {
        $this->attributes['orderData'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getOrderDataAttribute()
    {
        return json_decode($this->attributes['orderData']);
    }

    /**
     * @param $value
     */
    public function setOrderDocumentsAttribute($value)
    {
        $this->attributes['orderDocuments'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getOrderDocumentsAttribute()
    {
        return json_decode($this->attributes['orderDocuments']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function user()
    {
        return $this->hasOne(User::class,'userId', 'userId');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function holder()
    {
        return $this->hasOne(User::class,'userId', 'holdingId');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function complex()
    {
        return $this->belongsTo(Orders::class,'id', 'complexOrderId');
    }

    public function formatForList()
    {
        $results = [];
        $user = $this->user;

        foreach ($this->orderData->result->ReservationResults as $result){
            $results[] = [
                'index' => $result->Index,
                'itemId' => $result->OrderItemId,
                'from' => isset($result->OriginStation) ? $result->OriginStation : null,
                'to' => isset($result->DestinationStation) ? $result->DestinationStation : null,
                'departure' => [
                    'msk' => isset($result->DepartureDateTime) ? $result->DepartureDateTime : null,
                    'local' => isset($result->LocalDepartureDateTime) ? $result->LocalDepartureDateTime : null,
                ],
                'arrival' => [
                    'msk' => isset($result->ArrivalDateTime) ? $result->ArrivalDateTime : null,
                    'local' => isset($result->LocalArrivalDateTime) ? $result->LocalArrivalDateTime : null
                ],
                'class' => isset($result->ServiceClass) ? $result->ServiceClass : null,
                'passengersCount' => count($result->Passengers),
                'cancelTill' => isset($result->DepartureDateTime) ? date('Y-m-d H:i:s', strtotime("{$result->DepartureDateTime} - 2 minute")) : null
            ];
        }

        return [
            'id' => $this->orderId,
            'type' => Orders::ORDER_TYPE_RAILWAY,
            'typeName' => Orders::$type_name[Orders::ORDER_TYPE_RAILWAY],
            'status' => static::$status_name[$this->orderStatus],
            'results' => $results,
            'user' =>  $user ? $user->contacts : null,
            'payTill' => $this->orderStatus > 1 ? null : $this->payTill,
            'amount' => $this->Amount,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s')
        ];
    }
}