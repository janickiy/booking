<?php

/*
 * class RK_Sirena_Response_TicketingAction
 * ¬вод информации об оплате (1-й этап)
 */


class RK_Sirena_Request_TicketingQuery extends RK_Sirena_Request
{
    /**
     * @var array
     */
    private $_params;

    /**
     * @var RK_Avia_Entity_Passenger
     */

    private $_firstPassengers;

    /**
     * @var string
     */

    private $_PNR;

    /**
     * ћетод оплаты
     * @var string
     */

    private $_formPay;


    /**
     * @param RK_Avia_Entity_Booking $booking
     */
    public function __construct(RK_Avia_Entity_Booking $booking)
    {

        $airlines = array();
        foreach ($booking->getSegments() as $segment) {
            $airlines[] = $segment->getMarketingCompanyCode();
        }

        $this->_formPay = array_intersect(array('7R','6R'), $airlines) ? 'CA' : 'IN';

        $this->_PNR = $booking->getLocator();
        $passengers = $booking->getPassengers();
        $this->_firstPassengers = $passengers[0];

        // ”становка поискового запроса
        $this->setRequest($this->buildRequest());
    }


    // TODO вынести в отдельный класс с генерацией параметров
    public function buildRequest()
    {
        $request = '<?xml version="1.0" encoding="utf-8"?>
            <sirena>
                <query>
                    <payment-ext-auth>
                        <surname>' . iconv("cp1251", "utf-8", $this->_firstPassengers->getLastname()) . '</surname>
                        <regnum>' . $this->_PNR . '</regnum>
                        <action>query</action>
                        <paydoc>
                            <formpay>'.$this->_formPay.'</formpay>
                        </paydoc>
                        <request_params>
                            <tick_ser>'.iconv("cp1251", "utf-8", "ЁЅћ").'</tick_ser>
                        </request_params>
                        <answer_params>
                            <lang>en</lang>
                        </answer_params>
                    </payment-ext-auth>
                </query>
              </sirena>';

        return new SimpleXMLElement($request);
    }

    /**
     * @param $key
     * @param $value
     */
    public function addParam($key, $value)
    {
        $this->_params[(string)$key] = $value;
    }

    /**
     * @param $key
     * @return null
     */
    public function getParam($key)
    {
        return isset($this->_params[(string)$key]) ? $this->_params[(string)$key] : null;
    }

}