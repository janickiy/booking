<?php

namespace ReservationKit\src\Modules\S7AgentAPI\Model;

use ReservationKit\src\Modules\Base\Model\Abstracts\AbstractClient;

// WSSE библиотека
require_once(dirname(__FILE__) . '/../../Base/Model/WSSE/soap-wsse.php');
// Используется из-за наличия функции записи логов
require_once $_SERVER['DOCUMENT_ROOT'] . '/common/scripts/phpfuncs.php';

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
            //'POST https://qa-gaia.s7.ru/agent-api/gaia HTTP/1.1',
            //'POST /agent-api/gaia HTTP/1.1',
            'Accept-Encoding: gzip,deflate',
            'Host: qa-gaia.s7.ru',
            'Content-Type: text/xml;charset=UTF-8',
            //'Accept: text/xml',
            'Cache-Control: no-cache',
            'Pragma: no-cache',
            //'User-Agent: Apache-HttpClient/4.1.1 (java 1.5)',
            'SOAPAction: "http://api.s7.ru/SearchFlights"',
            'Authorization: Basic dGFsYXJpaTo1clVjQURVczd1a3U='
        ));
    }

    public function send()
    {
        $ch = curl_init();

        try {
            if ($ch === false) {
                throw new Exception('Ошибка инициализации Curl');
            }

            $ca = dirname(__FILE__) . '/../secure/qa_clientcert_trivago.pem';
            $key  = dirname(__FILE__) . '/../secure/keyS7.pem';

            $cert = dirname(__FILE__) . '/../secure/testS7.file.crt.pem';
            $key  = dirname(__FILE__) . '/../secure/testS7.file.key.pem';
/*
            $doc = new \DOMDocument('1.0');
            $doc->loadXML($this->getRequest());

            $wsse = new \WSSESoap($doc);
            //$wsse->addUserToken('trivago', '5rUcADUs7uku');
            // Добавляем в запрос сертификат
            $bin_token = $wsse->addBinaryToken(file_get_contents($cert));
            // Получаем private ключ для подписи
            $sec_key = new \XMLSecurityKey(\XMLSecurityKey::RSA_SHA1, array('type' => 'private', 'library' => 'openssl'));
            $sec_key->loadKey($key, true);
            // Добавляем цифровую подпись в запрос
            $wsse->signSoapDoc($sec_key);
            // Добавляем в цифровую подпись ссылку на сертификат
            $wsse->attachTokentoSig($bin_token);


            $this->setRequest($wsse->saveXML());*/

            $this->addRequestHeader('Content-length: ' . strlen($this->getRequest()));

            curl_setopt_array($ch, array(
                CURLOPT_URL            => $this->_location,
                //CURLOPT_HEADER         => 0,
                //CURLOPT_HTTPAUTH       => CURLAUTH_BASIC,
                //CURLOPT_USERPWD        => $this->_parameters['login'] . ':' . $this->_parameters['password'],
                //CURLOPT_SSL_VERIFYPEER => 0,
                //CURLOPT_SSL_VERIFYHOST => 2,

                //CURLOPT_USERAGENT      => 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)',

                CURLOPT_TIMEOUT        => 90,
                CURLOPT_POST           => true,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => $this->getRequestHeaders(),
                CURLOPT_POSTFIELDS     => $this->getRequest(),
            ));

            //curl_setopt($ch, CURLOPT_SSLVERSION, 6);

            //var_dump(is_file(dirname(__FILE__) . '/../secure/qa_clientcert_trivago.pem'));
            //var_dump(is_file(dirname(__FILE__) . '/../secure/keyS7.pem'));

            //curl_setopt($ch, CURLOPT_SSLCERT, dirname(__FILE__) . '/../secure/qa_clientcert_trivago.pem');
            //curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . '/../secure/key_store_S7.p12');
            //curl_setopt($ch, CURLOPT_SSLKEY, dirname(__FILE__) . '/../secure/keyS7.pem');
            //curl_setopt($ch, CURLOPT_SSLCERTPASSWD, '5rUcADUs7uku');
            //curl_setopt($ch, CURLOPT_SSLKEYPASSWD, '5rUcADUs7uku');
            //curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'P12');

            //curl_setopt($ch, CURLOPT_SSLKEY, $key);
            //curl_setopt($ch, CURLOPT_CAINFO, $ca);
            //curl_setopt($ch, CURLOPT_SSLCERT, $cert);
            //curl_setopt($ch, CURLOPT_SSLCERTPASSWD, '5rUcADUs7uku');

            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
            //curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);         # IF requires basic auth
            curl_setopt($ch, CURLOPT_USERPWD, 'trivago:5rUcADUs7uku');  # The login and password
/*
            $this->setResponse(curl_exec($ch));
            $this->setResponseHeaders(curl_getinfo($ch));

            pr($ch);

            $err     = curl_errno($ch);
            $errmsg  = curl_error($ch);

            //pr($this->_parameters);

            pr($err);
            pr($errmsg);

            pr('headers ----+');

            pr($this->getRequestHeaders());
            pr($this->getResponseHeaders());

            pr($this->getResponse());

            */

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