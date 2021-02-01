<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    protected $table = 'password_resets';
    protected $primaryKey = 'userId';

    public $timestamps = false;

    protected $fillable = [
      'token',
      'userId'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }
}