<?php

namespace App\Models\Admin\Hotel;

use Trivago\Hotels\Modules\Models\Region;

class HotelRegion extends Region
{
    protected $table = 'hotels_regions';

    protected $hidden = [];

    public function parent()
    {
        return $this->belongsTo($this, 'parent_id','id');
    }

}
