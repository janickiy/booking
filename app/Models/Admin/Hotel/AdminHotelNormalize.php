<?php

namespace App\Models\Admin\Hotel;

use Trivago\Hotels\Modules\Models\HotelNormalize;

class AdminHotelNormalize extends HotelNormalize
{
    protected $table = 'hotels_hotels_normalize';

    protected $hidden = [];

}
