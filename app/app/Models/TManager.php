<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\References\Traits\Cache;

class TManager extends Model
{
    use Cache;

    protected $table = 'ltm_translations';
    protected $primaryKey = 'id';
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $fillable = [
        'locale',
        'group',
        'key',
        'value'
    ];

    /**
     * @param $query
     * @param $group
     * @return mixed
     */
    public function scopeOfTranslatedGroup($query, $group)
    {
        return $query->where('group', $group)->whereNotNull('value');
    }

    /**
     * @param $query
     * @param $ordered
     * @return mixed
     */
    public function scopeOrderByGroupKeys($query, $ordered) {
        if ($ordered) {
            $query->orderBy('group')->orderBy('key');
        }

        return $query;
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeSelectDistinctGroup($query)
    {
        $select = 'DISTINCT "group"';

        return $query->select(\DB::raw($select));
    }
}