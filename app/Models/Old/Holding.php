<?php

namespace App\Models\Old;


use Illuminate\Database\Eloquent\Model;

class Holding extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'users_holdings';
    protected $primaryKey = 'id';

}