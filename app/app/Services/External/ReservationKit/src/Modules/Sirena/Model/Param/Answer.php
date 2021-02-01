<?php

/**
 * Параметры запроса
 * http://private.sirena-travel.ru/clients/manuals/xml-grs/
 * Таблица 32. Параметры элемента <answer_params>:
 *
 */
class RK_Sirena_Param_Answer extends RK_Sirena_Param
{
    private $_show_upt_rec = false;

    /**
     * @param mixed $show_upt_rec
     */
    public function setShowUptRec($show_upt_rec)
    {
        $this->_show_upt_rec = $show_upt_rec;
    }

    public function getXML()
    {
        $ar_params = array(
            "show_flighttime"   => "true",
            "show_io_matching"  => "true",
            "show_upt_rec"      => $this->_show_upt_rec?"true":"false",
            "show_et"           => "true",
            "lang"              => "en",
            "show_available"    => "true",
            "show_varianttotal" => "true"
        );

        $xml_param = array();
        foreach ($ar_params as $name => $value) {
            $xml_param[] = $this->createXMLElement($name, $value);
        }

        return $this->createXMLElement("answer_params", implode("", $xml_param));
    }
}