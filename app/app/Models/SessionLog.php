<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 05.07.2018
 * Time: 12:29
 */

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class SessionLog extends Model
{
    protected $connection = 'log';
    protected $table = 'session_log';
    protected $primaryKey = 'session_log_id';

    protected $fillable = [
        'session_id',
        'user_id',
        'referer',
        'path',
        'route',
        'request',
        'response',
        'response_code',
        'external',
        'queries',
        'log_start_time',
        'log_end_time',
    ];

    public function setRequestAttribute($value)
    {
        $this->attributes['request'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function getRequestAttribute($value)
    {
        return json_decode($value);
    }

    public function setResponseAttribute($value)
    {
        $this->attributes['response'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function getResponseAttribute($value)
    {
        return json_decode($value);
    }

    public function setExternalAttribute($value)
    {
        $this->attributes['external'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function getExternalAttribute($value)
    {
        return json_decode($value);
    }

    public function setQueriesAttribute($value)
    {
        $this->attributes['queries'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function getQueriesAttribute($value)
    {
        return json_decode($value);
    }

    public function user()
    {
        return $this->hasOne(User::class,'userId', 'user_id');
    }
}