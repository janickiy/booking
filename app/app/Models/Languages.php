<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Languages extends Model
{
    protected $table = 'ltm_languages';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'locale',
        'hide'
    ];

    public function scopeHide($query)
    {
        return $query->where('hide', 'false');
    }

}