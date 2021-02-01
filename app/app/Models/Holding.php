<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Holding extends Model
{
    protected $table = 'holdings';
    protected $primaryKey = 'holdingId';

    protected $fillable = [
        'holdingId',
        'name'
    ];

}