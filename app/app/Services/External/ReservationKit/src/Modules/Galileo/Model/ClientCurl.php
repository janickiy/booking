<?php

namespace ReservationKit\src\Modules\Galileo\Model;

use ReservationKit\src\Modules\Base\Model\Abstracts\AbstractClient;

class ClientCurl extends AbstractClient
{
    private $_location = null;

    private $_parameters = array();

    public function __construct($location, array $parameters = array())
    {
        $this->_location   = $location;
        $this->_parameters = $parameters;

        $this->setRequestHeaders(array(
            // https://emea.universal-api.pp.travelport.com/B2BGateway/connect/uAPI/AirService
            //'POST https://emea.universal-api.pp.travelport.com/B2BGateway/connect/uAPI/AirService HTTP/2.0',
            //'Accept-Encoding: gzip,deflate',
            'Content-Type: text/xml;charset=UTF-8',
            'Accept: text/xml',
            'Cache-Control: no-cache',
            'Pragma: no-cache',
            'SOAPAction: ""',
        ));
    }

    public function send()
    {
        $ch = curl_init();

        try {
            if ($ch === false) {
                throw new Exception('Ошибка инициализации Curl');
            }

            $this->addRequestHeader('Content-length: ' . strlen($this->getRequest()));

            curl_setopt_array($ch, array(
                CURLOPT_URL            => $this->_location,
                CURLOPT_HEADER         => 0,
                CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
                CURLOPT_USERPWD        => $this->_parameters['login'] . ':' . $this->_parameters['password'],
                CURLOPT_SSL_VERIFYPEER => 0,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_TIMEOUT        => 90,
                CURLOPT_POST           => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => $this->getRequestHeaders(),
                CURLOPT_POSTFIELDS     => $this->getRequest(),
            ));

            $this->setResponse(curl_exec($ch));
            $this->setResponseHeaders(curl_getinfo($ch));

            //$err     = curl_errno($ch);
            //$errmsg  = curl_error($ch);

            // Логи TODO продумать и унифицировать для всех систем, чтобы все писалось из одного метода. Вынести в отдельный метод.
            /*
            $securePath = '/../logs/galileo' . date('/Y/m/d/');
            $gRanStr = $securePath . gRanStr() . '_' . $functionname;
            if (!file_exists(RK_ROOT_PATH . $securePath)) { mkdir(RK_ROOT_PATH . $securePath, 0777, true); }
            file_put_contents(RK_ROOT_PATH . $gRanStr . '_RQ.xml', $request);
            file_put_contents(RK_ROOT_PATH . $gRanStr . '_RS.xml', $this->editResponseContent($response['data']));
            file_put_contents(RK_ROOT_PATH . $gRanStr . '_WS.xml', $requestXml);
            */
            //WriteLog(array('response' => $this->editResponseContent($response['data'])), $request_id);

        } catch(Exception $e) {
            // Запись ошибки в логи
            //WriteLog(array('type' => 'Exception', 'response' => $e->getMessage()), $request_id);

            //trigger_error(sprintf( 'Curl failed with error #%d: %s', $e->getCode(), $e->getMessage()), E_USER_ERROR);
        }

        curl_close($ch);

        if (!$this->getResponse()) {
            throw new Exception('Empty response');
        }
    }
}