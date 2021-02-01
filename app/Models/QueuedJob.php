<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 20.03.2018
 * Time: 13:46
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class QueuedJob extends Model
{
    protected $table = 'jobs';
    protected $primaryKey = 'id';

    public $timestamps = false;
}