<?php

namespace ReservationKit\src\Component\HttpFoundation\URL;

class URL
{
    protected $_params = array();
    protected $_absolute = false;
    protected $_secured = false;
    protected $_path;
    protected $_host;
    protected $_route;
    protected $_port;

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * Добавить параметр
     *
     * @param string $key
     * @param string $value
     * @return URL
     */
    public function addParam($key, $value)
    {
        //TODO: добавить rawurlencode или urlencode где нужно
        $this->_params[$key] = $value;
        return $this;
    }

    /**
     * Устанавливает массив параметров
     *
     * @param array $array
     * @return URL
     */
    public function addParams($array)
    {
        foreach ($array as $key => $value) {
            $this->addParam($key, $value);
        }
        
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRoute()
    {
        return $this->_route;
    }

    /**
     * Маршрут для страницы, параметр route
     *
     * @param string $route
     * @return URL
     */
    public function setRoute($route)
    {
        $this->_route = $route;
        return $this;
    }

    /**
     * Возвращает хост
     *
     * @return mixed
     */
    public function getHost()
    {
        return $this->_host;
    }

    /**
     * Устанавливает хост
     *
     * @param string $host
     * @return URL
     */
    public function setHost($host)
    {
        $this->_host = $host;
        
        return $this;
    }

    /**
     * HTTPS
     * 
     * @param boolean $switch
     */
    public function securedURL($switch)
    {
        $this->_secured = (bool) $switch;
    }

    /**
     * Возвращает путь
     * 
     * @return mixed
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * Задать путь от хоста, например /index.php или /index/param1/value1/
     *
     * @param string $path
     * @return URL
     */
    public function setPath($path)
    {
        $this->_path = $path;
        
        return $this;
    }
    /**
     *
     * @return string
     */
    public function getPort()
    {
        return $this->_port;
    }
    /**
     *
     * @param string $port
     */
    public function setPort($port)
    {
        $this->_port = $port;
    }

    /**
     * Адрес URL
     *
     * @return string
     */
    public function getURL()
    {
        return $this->__toString();
    }

    /**
     * Возвращает тип URL
     *
     * @return bool
     */
    public function getAbsolute()
    {
        return $this->_absolute;
    }

    /**
     * Задает относительный или абсолютный URL
     *
     * @param $absolute
     */
    public function setAbsolute($absolute)
    {
        $this->_absolute = $absolute;
    }


    /**
     * Сброс GET параметров
     *
     * @return URL
     */
    public function resetParams()
    {
        $this->_params = array();
        
        return $this;
    }

    /**
     * Разбор текущей URL
     *
     * @return URL
     */
    public function parseCurrentURL()
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            $this->setHost($_SERVER['HTTP_HOST']);
        }
        
        if (isset($_SERVER['PHP_SELF'])) {
            $this->setPath($_SERVER['PHP_SELF']);
        }
        
        foreach ($_GET as $key => $val) {
            if ($key === 'route') {
                $this->setRoute($val);
            } else {
                $this->addParam($key, $val);
            }
        }
        
        return $this;
    }

    /**
     * Разбор строки URL, если вызывается без аргументов - парсит текущий URL
     *
     * @param string $url
     * @return URL
     */
    public function parseURL($url = null)
    {
        if (!$url) {
            return $this->parseCurrentURL();
        }
        
        $urlParams = parse_url($url);
        $this->_secured = (isset($urlParams['scheme']) && $urlParams['scheme'] === 'https');
        
        if (isset($urlParams['host'])) {
            $this->_host = $urlParams['host'];
            $this->_absolute = true;
        }
        
        if(isset($urlParams['port'])) {
            $this->setPort($urlParams['port']);
        }
        
        if (isset($urlParams['path'])) {
            $this->_path = $urlParams['path'];
        }
        
        if (isset($urlParams['query'])) {
            $this->parseQueryString($urlParams['query']);
        }
        
        return $this;
    }

    protected function parseQueryString($string)
    {
        $params = explode('&', $string);
        foreach ($params as $param) {
            $values = explode('=', $param);
            
            if ($values) {
                $key = reset($values);
                $val = next($values);
                
                if ($key === 'route') {
                    $this->setRoute($val);
                } else {
                    $this->addParam($key, $val);
                }
            }
        }
    }

    /**
     * Генерация URL, получение текстового представления
     *
     * @return string
     */
    public function __toString()
    {
        $url = '';
        if ($this->_absolute) {
            $url .= ( $this->_secured) ? 'https://' : 'http://';
            $url .= ( $this->_host) ? $this->_host : $_SERVER['HTTP_HOST'];
            
            if($this->getPort()) {
                $url .= ':' . $this->getPort();
            }
        }
        
        $url .= ( $this->_path) ? $this->_path : $_SERVER['PHP_SELF'];
        $params = $this->_params;
        
        if ($this->_route) {
            $params = array_merge(array('route' => $this->_route), $this->_params);
        }
        
        $first = true;
        foreach ($params as $key => $val) {
            $url .= ( $first) ? '?' : '&';
            $url .= $key;
            
            if ($val !== null && $val !== '') {
                $url .= '=' . $val;
            }
            
            $first = false;
        }
        
        return $url;
    }
}