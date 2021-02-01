<?php

namespace App\Models\Admin\Hotel;

use Trivago\Hotels\Modules\Models\Hotel;

class AdminHotel extends Hotel
{
    protected $table = 'hotels_hotels';

    protected $hidden = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function regions()
    {
        return $this->belongsTo(HotelRegion::class, 'region_id','id');
    }
}
