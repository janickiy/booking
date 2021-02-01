<?php

namespace App\Models\Traits;

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