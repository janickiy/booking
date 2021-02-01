<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 08.02.2019
 * Time: 11:58
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'portal_roles';
    protected $primaryKey = 'roleId';

    protected $fillable = [
        'name',
        'description',
        'accessMask',
        'services'
    ];

    public static $can = [
        'r' => 1 << 0,
        'w' => 1 << 1,
        'c' => 1 << 2,
        'd' => 1 << 3
    ];

    public static $servicesDefaults =  [
        'avia'=> [
            'systems'=> [
                'S7'=> true,
                'Galileo'=> true,
                'Sirena'=> true
            ],
            'enabled'=> true
        ],
        'railway'=> [
            'systems'=> [
                'IM'=> true
            ],
            'enabled'=> true
        ],
        'aeroexpress'=> [
            'systems'=> [
                'IM'=> true
            ],
            'enabled'=> false
        ],
        'hotels'=> [
            'systems'=> [
                'ostrovok'=> true,
                'zabroniryi'=> true,
                'academservice'=> true,
                'goglobal'=> true,
                'trivago'=> true
            ],
            'enabled'=> true
        ],
        'ensurance'=> [
            'systems'=> [
                'Alfa'=> true
            ],
            'enabled'=> true
        ],
        'car'=>[
            'systems'=> [
                'RentalCar'=> true
            ],
            'enabled'=> true
        ],
        'bus'=>[
            'systems'=> [
                'IM'=> true
            ],
            'enabled'=> true
        ]
    ];

    /**
     * @param $value
     */
    public function setAccessMaskAttribute($value)
    {
        $this->attributes['accessMask'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getAccessMaskAttribute()
    {
        return json_decode($this->attributes['accessMask']);
    }

    public function setServicesAttribute($value)
    {
        $this->attributes['services'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function getServicesAttribute()
    {
        return json_decode($this->attributes['services']);
    }
}