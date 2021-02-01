<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\S7AgentAPI\Model\Interfaces\IRequest;

abstract class Request implements IRequest
{
    private $_xmlns_soapenv2 = 'http://schemas.xmlsoap.org/soap/envelope/';

    private $_xmlns_soapenv3 = 'http://www.iata.org/IATA/EDIST';

    /**
     * Запрос
     *
     * @var XmlElement
     */
    protected $_request;

    /**
     * Запрос
     *
     * @var XmlElement
     */
    protected $_response;

    /**
     * Параметры запроса, из которых он состоит
     *
     * @var array
     */
    private $_params = array();

    /**
     * Дополнительные параметры
     * TODO переделать
     */
    private $_options = array();

    /**
     * TODO переделать
     */
    private $_logs;

    /**
     * Генерация запроса
     *
     * Запрос конструируется из параметров массива $_params, элементами которого являются объекты XmlElement
     */
    public function __construct()
    {
        $attributesEnvelope = array(
            'xmlns:ns2' => $this->_xmlns_soapenv2,
            'xmlns:ns3' => $this->_xmlns_soapenv3
        );

        $envelope =
        //new XmlElement('Envelope', $attributesEnvelope, array(
            //new XmlElement('Header', array(), null, 'ns2'),
            //new XmlElement('Body', array(),
                new XmlElement($this->getWSDLServiceName(), $this->getFunctionAttributes(), $this->_params, 'ns1');//, 'ns2'
            //)
        //), 'ns2');

        $this->setRequest($envelope);
    }

    /**
     * @return XMLElement
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Установка готового запроса
     *
     * Используется классами-потомками
     *
     * @param XMLElement $request
     */
    public function setRequest(XMLElement $request)
    {
        $this->_request = $request;
    }

    /**
     * @return XmlElement
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * @param \SimpleXmlElement $response
     */
    public function setResponse($response)
    {
        $this->_response = $response;
    }

    /**
     * @param $key
     * @param $value
     */
    public function addParam($key, $value)
    {
        $this->_params[(string) $key] = $value;
    }

    /**
     * @param $key
     * @return null
     */
    public function getParam($key)
    {
        return isset($this->_params[(string) $key]) ? $this->_params[(string) $key] : null;
    }

    /**
     * @return \SimpleXMLElement
     * @throws \ReservationKit\src\Modules\Galileo\Model\GalileoException
     */
    public function send()
    {
        try {
            $client = new ClientSoap(Requisites::getInstance()->getRequestURI() . '/' . $this->getWSDLServiceName(),
                array(
                    'login'    => Requisites::getInstance()->getLogin(),
                    'password' => Requisites::getInstance()->getPassword()
                )
            );

            $request = $this->getRequest()->getXml();

            // checkXMLError

            $request_time = (int) number_format(microtime(true), 12, '.', '');
            $request_hash = sha1(time() . createBase64UUID());

            // Удаление ns из нодов
            $requestForLogs = preg_replace(array('/<([A-Za-z-_0-9]+:)/i', '/<\/([A-Za-z-_0-9]+:)/i'), array('<', '</'), $request);
            $requestForLogs = $request;

            $logData = array(
                'system'       => 'S7Agent',
                //'request'      => prettyXML($request),
                'request'      => prettyXML(str_replace('&', '&amp;', $requestForLogs)),
                'transaction'  => $this->getWSDLFunctionName(),
                'request_hash' => $request_hash
            );

            if ($this->getOptionByKey('remote_ip')) {
                $logData['user_ip'] = $this->getOptionByKey('remote_ip');
            }

            $request_id = WriteLog($logData);

            $this->_logs = array(
                'request_id'   => $request_id,
                'request_hash' => $request_hash,
                'time'         => $request_time
            );

            $client->setRequest($request);
            $client->send($this->getWSDLFunctionName());

            // Логи. Заполнить и записать действие, запрос, ответ, систему и т.д.

            // Обработка ответа
            // Удаление ns из нодов
            $response = preg_replace(array('/<([A-Za-z-_0-9]+:)/i', '/<\/([A-Za-z-_0-9]+:)/i'), array('<', '</'), $client->getResponse());

            // @see problem SimpleXmlElement and ampersand
            $response = str_replace('&', '&amp;', $response);

            //WriteLog(array('response' => str_replace('&amp;', '&', prettyXML($response))), $request_id);

            /*
            $securePath = '/../logs/s7api' . date('/Y/m/d/');pr(RK_ROOT_PATH . $securePath);
            $gRanStr = $securePath . gRanStr() . '_' . $this->getWSDLFunctionName();
            if (!file_exists(RK_ROOT_PATH . $securePath)) { mkdir(RK_ROOT_PATH . $securePath, 0777, true); }
            file_put_contents(RK_ROOT_PATH . $gRanStr . '_RQ.xml', prettyXML($request));
            file_put_contents(RK_ROOT_PATH . $gRanStr . '_RS.xml', prettyXML($response));
            */

            return new \SimpleXMLElement($response);

        } catch (\Exception $e) {
            // Запись ошибки в логи
            $logData = array(
                'system'      => 'S7Agent',
                'type'        => 'Exception',
                'response'    => $e->getMessage(),
                'transaction' => $this->getWSDLFunctionName(),
            );

            if ($this->getOptionByKey('remote_ip')) {
                $logData['user_ip'] = $this->getOptionByKey('remote_ip');
            }

            //WriteLog($logData, $request_id);

            $this->_logs = array(
                'request_id'   => $request_id,
                'request_hash' => $request_hash,
                'time'         => $request_time
            );
        }
    }

    /**
     * @return mixed
     */
    public function getLogs()
    {
        return $this->_logs;
    }

    /**
     * @param string $key
     * @return array
     */
    public function getOptionByKey($key)
    {
        return isset($this->_options[$key]) ? $this->_options[$key] : null;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function addOption($key, $value)
    {
        $this->_options[$key] = $value;
    }
}