<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class AdminUserRole extends Model
{
    protected $table = 'admin_user_roles';
    protected $primaryKey = 'adminUserRoleId';

    protected $fillable = [
        'adminUserId',
        'adminRoleId',
        'validTill'
    ];
}