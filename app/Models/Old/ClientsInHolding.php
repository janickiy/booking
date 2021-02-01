<?php

namespace App\Models\Old;

use Illuminate\Database\Eloquent\Model;

class ClientsInHolding extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'users_holdings_clients';
    protected $primaryKey = 'holding_id';
}