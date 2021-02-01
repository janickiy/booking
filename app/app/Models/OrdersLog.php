<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 10.02.2019
 * Time: 14:15
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class OrdersLog extends Model
{
    protected $connection = 'log';
    protected $table = 'orders_log';
    protected $primaryKey = 'orders_log_id';

    protected $fillable = [
        'session_log_id',
        'order_id',
        'action',
        'message',
        'payload',
        'error'
    ];

    public function sessionLog()
    {
        return $this->hasOne(SessionLog::class,'session_log_id','session_log_id');
    }


    public function setPayloadAttribute($value)
    {
        $this->attributes['payload'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function getPayloadAttribute($value)
    {
        return json_decode($value);
    }

}