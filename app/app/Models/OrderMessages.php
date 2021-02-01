<?php

namespace App\Models;

use App\Models\Admin\AdminUser;
use Illuminate\Database\Eloquent\Model;

class OrderMessages extends Model
{
    protected $table = 'order_messages';
    protected $primaryKey = 'id';

    protected $fillable = [
        'order_id',
        'order_type',
        'order_item_id',
        'sender_id',
        'receiver_id',
        'message',
        'status'
    ];

    /**
     * @return mixed
     */
    public function sender()
    {
        return $this->hasOne(User::class,'userId', 'sender_id');
    }

    /**
     * @return mixed
     */
    public function receiver()
    {
        return $this->hasOne(AdminUser::class,'adminUserId', 'receiver_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

}
