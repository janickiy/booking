<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 14.08.2018
 * Time: 10:39
 */

namespace App\Models\Admin;


use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class AdminUser extends Authenticatable
{
    use Notifiable;

    protected $table = 'admin_users';
    protected $primaryKey = 'adminUserId';

    protected $fillable = [
        'email',
        'login',
        'name',
        'password',
        'allowedIp'
    ];

    protected $accessMethodsMap = [
        'GET' => 'r',
        'POST' => 'c',
        'PATCH' => 'w',
        'PUT' => 'w',
        'DELETE' => 'd'
    ];

    public function setAllowedIpAttribute($value)
    {
        $this->attributes['allowedIp'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function getAllowedIpAttribute($value)
    {
        return json_decode($value);
    }

    public function roles()
    {
        return $this->belongsToMany(AdminRole::class, 'admin_user_roles','adminUserId', 'adminRoleId', 'adminUserId', 'adminRoleId');
    }

    public function hasAccess($route, $method='GET')
    {
        $unionAccessMap = [];

        foreach ($this->roles as $role){
            $unionAccessMap = array_merge($unionAccessMap, $role->accessMap);
        }

        if(in_array('all', array_keys($unionAccessMap))) return true;

        if(in_array($route, $unionAccessMap) && in_array($this->accessMethodsMap[$method],$unionAccessMap[$route])) return true;

        return false;
    }

    public function role()
    {
        return $this->hasMany(AdminUserRole::class,'adminUserId','adminUserId');
    }
}