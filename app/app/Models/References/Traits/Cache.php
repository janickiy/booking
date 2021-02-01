<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 09.04.2018
 * Time: 15:00
 */

namespace App\Models\References\Traits;


use Watson\Rememberable\Rememberable;

trait Cache
{
    use Rememberable;
    protected $rememberFor = 60*24;
    protected $rememberCacheTag = __CLASS__;

    public function getTag()
    {
        return $this->rememberCacheTag;
    }
}