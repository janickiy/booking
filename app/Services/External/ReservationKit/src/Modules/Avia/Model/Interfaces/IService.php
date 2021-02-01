<?php

namespace ReservationKit\src\Modules\Avia\Model\Interfaces;

interface IService
{
    public function search(\RK_Avia_Entity_Search_Request $searchRequest);


}