<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 20.08.2018
 * Time: 11:09
 */

namespace App\Models\Admin;


use Illuminate\Database\Eloquent\Model;

class AdminRole extends Model
{
    protected $table = 'admin_roles';
    protected $primaryKey = 'adminRoleId';

    protected $fillable = [
        'accessLevel',
        'accessMap',
        'name'
    ];

    protected $visible = [
        'adminRoleId',
        'accessLevel',
        'accessMap',
        'name'
        ];

    public function setAccessMapAttribute($value)
    {
        $this->attributes['accessMap'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function getAccessMapAttribute($value)
    {
        return json_decode($value, true);
    }

    public function role()
    {
        return $this->belongsTo(AdminUserRole::class,'adminUserId','adminUserId');
    }


    public function users()
    {
        return $this->belongsToMany(AdminUser::class,'admin_user_roles', 'adminRoleId', 'adminUserId', 'adminRoleId', 'adminUserId');
    }
}