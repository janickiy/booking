<?php

class RK_Sirena_Param_Passenger extends RK_Sirena_Param
{
    public function __construct(array $passengers = null)
    {
        $this->addParam('passengers', $passengers);
    }

    public function getXML()
    {
        $ar_passengers_xml = array();

        foreach ($this->getPassengers() as $code => $count) {

            if ($code == "CNN") {
                $age  = $this->createXMLElement("age", 11);
            } elseif ($code == "INF" || $code == "INS") { // Здесь странно, конечно, на сайте с место или без, но нигде это не учитывается
                $age = $this->createXMLElement("age", 1);
            } else {
                $age = "";
            }

            $code   = $this->createXMLElement("code", $code);
            $count  = $this->createXMLElement("count", $count);

            $ar_passengers_xml[] = $this->createXMLElement('passenger', $code . $count . $age);
        }

        return implode("", $ar_passengers_xml);
    }

    private function getPassengers()
    {
        return $this->getParam('passengers');
    }
}