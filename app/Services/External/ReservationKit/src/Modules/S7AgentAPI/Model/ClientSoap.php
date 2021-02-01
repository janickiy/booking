<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model;

use ReservationKit\src\Modules\Base\Model\Abstracts\AbstractClient;

// WSSE библиотека
require_once(dirname(__FILE__) . '/../../Base/Model/WSSE/soap-wsse.php');

class ClientSoap extends AbstractClient
{
    private $_location = null;

    private $_parameters = array();

    public function __construct($location, array $parameters = array())
    {
        // Таймлимит ожидания ответа
        ini_set('default_socket_timeout', 45);

        $this->_location   = $location;
        $this->_parameters = $parameters;

        //$this->setRequestHeaders();
    }

    public function send($operationName)
    {
        try {
            $version = '0.52';

            $options = array(
                'login'    => 'trivago',
                'password' => 'WraxuPrAbuT8',
                //'password' => '5rUcADUs7uku',

                'trace'    => true,
                'stream_context' => stream_context_create(array(
                    'http' => array(
                        'header' => 'X-API-Version: ' . $version
                    ),
                )),
                'local_cert' => dirname(__FILE__) . '/prod_clientcert_trivago.pem',
                //'cache_wsdl' => WSDL_CACHE_NONE,

                //'exceptions'   => true,
                //'style'        => SOAP_RPC,
                //'use'          => SOAP_ENCODED,
                //'soap_version' => SOAP_1_2
            );

            $client = new \SoapClient('https://api.s7.ru/agent-api/wsdl/' . $version . '?wsdl', $options);
            //$client = new \SoapClient('https://qa-gaia.s7.ru/agent-api/wsdl/' . $version . '?wsdl', $options);

            $client->{$operationName}(new \SoapVar($this->getRequest(), XSD_ANYXML));

            $this->setResponseHeaders($client->__getLastResponseHeaders());
            $this->setResponse($client->__getLastResponse());

            // Обработка ответа
            // Удаление ns из нодов
            // TODO сделать общий метод для обработки ответов и поместить его в класс радотель
            $prepareResponse = preg_replace(array('/<([A-Za-z-_0-9]+:)/i', '/<\/([A-Za-z-_0-9]+:)/i'), array('<', '</'), $this->getResponse());
            $this->setResponse($prepareResponse);

        } catch(Exception $e) {
            // Запись ошибки в логи
            //WriteLog(array('type' => 'Exception', 'response' => $e->getMessage()), $request_id);

            //trigger_error(sprintf( 'Curl failed with error #%d: %s', $e->getCode(), $e->getMessage()), E_USER_ERROR);
        }

        if (!$this->getResponse()) {
            throw new Exception('Empty response');
        }
    }
}