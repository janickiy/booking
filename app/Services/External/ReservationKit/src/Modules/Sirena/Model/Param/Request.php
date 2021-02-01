<?php

/**
 * Параметры запроса
 * http://private.sirena-travel.ru/clients/manuals/xml-grs/#idm249336837440
 *
 */
class RK_Sirena_Param_Request extends RK_Sirena_Param
{
    public function getXML()
    {
        $ar_params = array(
            "fingering_order"   => "differentFlightsCombFirst",
            "tick_ser"          => iconv("cp1251", "utf-8", "ЭБМ"),
            "min_results"       => RK_Sirena_Requisites::getInstance()->isModeProd()?"150":"150", //25
            "max_results"       => RK_Sirena_Requisites::getInstance()->isModeProd()?"250":"250", //50
            "timeout"           => RK_Sirena_Requisites::getInstance()->isModeProd()?"150":"150", //25
            "et_if_possible"    => "true",
            "mix_scls"          => "true",
            "mix_ac"            => "true"
        );

        $xml_param = array();
        foreach ($ar_params as $name => $value) {
            $xml_param[] = $this->createXMLElement($name, $value);
        }

        return $this->createXMLElement("request_params", implode("", $xml_param));
    }
}