<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\StringHelpers;
use App\Models\Traits\Cache;

class Menu extends Model
{
    use Cache;

    const PER_PAGE = 1000;

    protected $table = 'menu';
    protected $primaryKey = 'id';

    protected $fillable = [
        'title',
        'menu_type',
        'item_id',
        'url',
        'status',
        'item_order',
        'parent_id',
    ];


    /**
     * @param $value
     */
    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getTitleAttribute($value)
    {
        return json_decode($value);
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeStatus($query)
    {
        return $query->where('status', 'true');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function itemurl()
    {
        return $this->belongsTo(Pages::class, 'item_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children(){
        return $this->hasMany(Menu::class, 'parent_id', 'id');
    }

    public function parent()
    {
        return $this->belongsTo($this, 'parent_id', 'id');
    }

    /**
     * @param string $lang
     * @return mixed
     */
    public function title()
    {
        $title = StringHelpers::ObjectToArray($this->title);

        return isset($title[config('app.locale')]) ? $title[config('app.locale')] : '';
    }
}