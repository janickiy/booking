<?php

namespace ReservationKit\src\Modules\Galileo\Model;

use ReservationKit\src\Component\XML\XmlElement;
use ReservationKit\src\Modules\Galileo\Model\Requisites;
use ReservationKit\src\Modules\Galileo\Model\ClientCurl;
use ReservationKit\src\Modules\Galileo\Model\GalileoException;
use ReservationKit\src\Modules\Galileo\Model\Param\WsseAuthHeader;
use ReservationKit\src\Modules\Galileo\Model\Interfaces\IRequest;

abstract class Request implements IRequest
{
    protected $_xmlns_soapenv = 'http://schemas.xmlsoap.org/soap/envelope/';
    protected $_xmlns_com     = 'http://www.travelport.com/schema/common_v40_0';
    protected $_xmlns_univ    = 'http://www.travelport.com/schema/universal_v40_0';

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
     * Запрос конструируется из параметров массива $_params,
     * элементами которого являются объекты XmlElement
     */
    public function __construct()
    {
        $attributesEnvelope = array(
            'xmlns:soapenv' => $this->_xmlns_soapenv,
            'xmlns:com'     => $this->_xmlns_com,
            'xmlns:univ'    => $this->_xmlns_univ
        );

        $envelope =
            new XmlElement('Envelope', $attributesEnvelope, array(
                new XmlElement('Body', array(),
                    new XmlElement($this->getWSDLFunctionName(), $this->getFunctionAttributes(),
                        $this->_params,
                    $this->getFunctionNameSpace()),
                'soapenv')),
            'soapenv');

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
            // TODO
            if (0 && $this->getWSDLFunctionName() === Request::FUNCTION_UniversalRecordModifyReq && Requisites::getInstance()->getRules()->getSearchPCC() === 'L8W') {
                $client = new ClientCurl(Requisites::getInstance()->getRequestURI() . '/' . $this->getWSDLServiceName(),
                    array(
                        'login' => 'Universal API/' . 'uAPI4316867519-de36d93f',
                        'password' => '2q%Ti_A7Kt'
                    )
                );

            } elseif ($this->getWSDLFunctionName() === Request::FUNCTION_AirTicketingReq && Requisites::getInstance()->getRules()->getSearchPCC() === 'L8W') {
                $client = new ClientCurl(Requisites::getInstance()->getRequestURI() . '/' . $this->getWSDLServiceName(),
                    array(
                        'login' => 'Universal API/' . 'uAPI4316867519-de36d93f',
                        'password' => '2q%Ti_A7Kt'
                    )
                );

            } else {
                $client = new ClientCurl(Requisites::getInstance()->getRequestURI() . '/' . $this->getWSDLServiceName(),
                    array(
                        'login' => 'Universal API/' . Requisites::getInstance()->getUserId(),
                        'password' => Requisites::getInstance()->getPassword()
                    )
                );
            }

            //
            if ($this->getWSDLFunctionName() === Request::FUNCTION_AirCreateReservationReq) {
                WriteLog(array(
                    'system'      => 'galileoUAPI',
                    'type'        => 'dump',
                    'request'     => $this->getRequest(),
                    'response'    => $this->getRequest()->getXml(),
                    'transaction' => 'checkRQ'
                ));
            }

            $request = $this->getRequest()->getXml();

            // checkXMLError

            $request_time = (int) number_format(microtime(true), 12, '.', '');
            $request_hash = sha1(time() . createBase64UUID());

            $logData = array(
                'system'       => 'galileoUAPI',
                //'request'      => prettyXML($request),
                'request'      => prettyXML(str_replace('&', '&amp;', $request)),
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
            $client->send();

            // Логи. Заполнить и записать действие, запрос, ответ, систему и т.д.

            // Обработка ответа
            // Удаление ns из нодов
            $response = preg_replace(array('/<([A-Za-z-_0-9]+:)/i', '/<\/([A-Za-z-_0-9]+:)/i'), array('<', '</'), $client->getResponse());

            // @see problem SimpleXmlElement and ampersand
            $response = str_replace('&', '&amp;', $response);

            WriteLog(array('response' => prettyXML($response)), $request_id);

            /*
            $securePath = '/../logs/galileo' . date('/Y/m/d/');
            $gRanStr = $securePath . gRanStr() . '_' . $this->getWSDLFunctionName();
            if (!file_exists(RK_ROOT_PATH . $securePath)) { mkdir(RK_ROOT_PATH . $securePath, 0777, true); }
            file_put_contents(RK_ROOT_PATH . $gRanStr . '_RQ.xml', prettyXML($request));
            file_put_contents(RK_ROOT_PATH . $gRanStr . '_RS.xml', prettyXML($response));
            */

            $response = new \SimpleXMLElement($response);

            $this->setResponse($response);

            return $this->getResponse();

        } catch (\Exception $e) {
            // Запись ошибки в логи
            $logData = array(
                'system'      => 'galileoUAPI',
                'type'        => 'Exception',
                'response'    => $e->getMessage(),
                'transaction' => $this->getWSDLFunctionName(),
            );

            if ($this->getOptionByKey('remote_ip')) {
                $logData['user_ip'] = $this->getOptionByKey('remote_ip');
            }

            WriteLog($logData, $request_id);

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