<?php

namespace ReservationKit\src\Modules\Base\Model\Abstracts;

abstract class AbstractClient
{
    private $_requestHeaders;

    private $_request;

    private $_responseHeaders;

    private $_response;

    /**
     * Заголовки запроса
     *
     * @return mixed
     */
    public function getRequestHeaders()
    {
        return $this->_requestHeaders;
    }

    public function setRequestHeaders($requestHeaders)
    {
        $this->_requestHeaders = $requestHeaders;
    }

    public function addRequestHeader($requestHeader)
    {
        if (is_array($this->_requestHeaders)) {
            $this->_requestHeaders[] = $requestHeader;

        } else if (is_string($this->_requestHeaders)) {
            $this->_requestHeaders .= $requestHeader;

        }
    }

    /**
     * Запрос
     *
     * @return mixed
     */
    public function getRequest()
    {
        return $this->_request;
    }

    public function setRequest($request)
    {
        $this->_request = $request;
    }

    /**
     * Заголовки ответа
     *
     * @return mixed
     */
    public function getResponseHeaders()
    {
        return $this->_responseHeaders;
    }

    public function setResponseHeaders($responseHeaders)
    {
        $this->_responseHeaders = $responseHeaders;
    }

    /**
     * Ответ
     *
     * @return mixed
     */
    public function getResponse()
    {
        return $this->_response;
    }

    public function setResponse($response)
    {
        $this->_response = $response;
    }
}