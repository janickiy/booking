<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 11.04.2019
 * Time: 16:41
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ClientDepartments extends Model
{

    protected $table = 'client_departments';
    protected $primaryKey = 'departmentId';

    protected $fillable = [
        'clientId',
        'outerDepartmentId',
        'name'
    ];
}