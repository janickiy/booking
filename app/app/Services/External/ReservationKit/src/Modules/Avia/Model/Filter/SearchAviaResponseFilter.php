<?php

namespace ReservationKit\src\Modules\Avia\Model\Helper;

class SearchAviaResponseFilter
{
    /**
     * @var \RK_Avia_Entity_Booking[]
     */
    private $_searchResponse;

    /**
     * @return \RK_Avia_Entity_Booking[]
     */
    public function getSearchResponse(): array
    {
        return $this->_searchResponse;
    }

    /**
     * @param \RK_Avia_Entity_Booking[] $searchResponse
     */
    public function setSearchResponse(array $searchResponse)
    {
        $this->_searchResponse = $searchResponse;
    }

    public function excludeSimilarOffers()
    {
        // TODO
    }
}