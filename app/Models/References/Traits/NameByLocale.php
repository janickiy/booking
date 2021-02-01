<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 12.04.2019
 * Time: 12:49
 */

namespace App\Models\References\Traits;


trait NameByLocale
{

    final public function __construct()
    {
        $this->appends[]='name';
        $this->visible[]='name';
        return parent::__construct();
    }

    public function getNameAttribute()
    {
        $nameSlug = 'name'.mb_convert_case(app()->getLocale(),MB_CASE_TITLE);
        $name = isset($this->custom) && !empty($this->custom->$nameSlug) ? $this->custom->$nameSlug : mb_convert_case($this->$nameSlug, MB_CASE_TITLE);
        if(empty($name)) $name = $this->nameEn;
        if(empty($name)) $name = $this->nameRu;
        return  $name;
    }
}