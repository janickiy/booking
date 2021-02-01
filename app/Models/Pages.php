<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\StringHelpers;
use App\Models\Traits\Cache;
use URL;

class Pages extends Model
{
    use Cache;

    protected $table = 'pages';
    protected $primaryKey = 'id';

    protected $fillable = [
        'title',
        'content',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'slug',
        'parent_id',
        'published',
        'page_path'
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
     * @param $value
     */
    public function setContentAttribute($value)
    {
        $this->attributes['content'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getContentAttribute($value)
    {
        return json_decode($value);
    }

    /**
     * @param $value
     */
    public function setMetaTitleAttribute($value)
    {
        $this->attributes['meta_title'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getMetaDescriptionAttribute($value)
    {
        return json_decode($value);
    }

    /**
     * @param $value
     */
    public function setMetaDescriptionAttribute($value)
    {
        $this->attributes['meta_description'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getMetaKeywordsAttribute($value)
    {
        return json_decode($value);
    }

    /**
     * @param $value
     */
    public function setMetaKeywordsAttribute($value)
    {
        $this->attributes['meta_keywords'] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopePublished($query)
    {
        return $query->where('published', 'true');
    }

    /**
     * @return string
     */
    public function getPublishedAttribute()
    {
        return $this->attributes['published'] ? 'публикован' : 'не опубликован';
    }

    /**
     * @return mixed
     */
    public function getStatusAttribute()
    {
        return $this->attributes['published'];
    }

    /**
     * @return mixed
     */
    public function getPagePathAttribute()
    {
        return $this->attributes['page_path'];
    }

    /**
     * @param string $lang
     * @return mixed
     */
    public function excerpt()
    {
        $content = StringHelpers::ObjectToArray($this->content);
        $content = preg_replace("/<img(.*?)>/si", "", $content[config('app.locale')]);
        $content = preg_replace('/(<.*?>)|(&.*?;)/', '', $content)  ;

        return StringHelpers::shortText($content,500);
    }

    /**
     * @return string
     */
    public function getPagePathTypeAttribute()
    {
        return $this->attributes['page_path'] ? 'Страница' : 'Раздел';
    }

    /**
     * @return string
     */
    public function getUrlPathAttribute()
    {
        return ($this->attributes['page_path'] ? 'page/' : 'path/') . $this->attributes['slug'];
    }

    /**
     * @return \Illuminate\Contracts\Routing\UrlGenerator|null|string
     */
    public function getApiUrlAttribute()
    {
        return $this->attributes['slug'] ?  URL::route('api.v1.frontend.page',['slaug' => $this->attributes['slug']]) : null;
    }

    /**
     * @return mixed
     */
    public function rootPage()
    {
        return $this->where('parent_id', 0)->with('catalog')->get();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo($this, 'parent_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children(){
        return $this->hasMany($this, 'parent_id', 'id');
    }

    /**
     * @return mixed
     */
    public function title_page()
    {
        $title = StringHelpers::ObjectToArray($this->title);

        return isset($title[config('app.locale')]) ? $title[config('app.locale')] : '';
    }

    /**
     * @return mixed
     */
    public function content()
    {
        $content = StringHelpers::ObjectToArray($this->content);

        return isset($content[config('app.locale')]) ? $content[config('app.locale')] : '';
    }

    /**
     * @return mixed
     */
    public function meta_title()
    {
        $meta_title = StringHelpers::ObjectToArray($this->meta_title);

        return isset($meta_title[config('app.locale')]) ? $meta_title[config('app.locale')] : '';
    }

    /**
     * @return mixed
     */
    public function meta_description()
    {
        $meta_description = StringHelpers::ObjectToArray($this->meta_description);

        return isset($meta_description[config('app.locale')]) ? $meta_description[config('app.locale')] : '';
    }

    /**
     * @return mixed
     */
    public function meta_keywords()
    {
        $meta_keywords = StringHelpers::ObjectToArray($this->meta_keywords);

        return isset($meta_keywords[config('app.locale')]) ? $meta_keywords[config('app.locale')] : '';
    }

    /**
     * @return mixed
     */
    public function getContentTypeAttribute()
    {
        return $this->attributes['page_path'];
    }
}