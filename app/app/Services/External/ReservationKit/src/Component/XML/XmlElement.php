<?php

namespace ReservationKit\src\Component\XML;

//use ReservationKit\src\Component\XML\Generator;

/**
 * Class XmlElement
 *
 * Примеры использования:
 * 1. Элеменет с атрибутом, с текстом и с неймспейсом:
 *    @example
 *      <ns1:node_name attr_name="attr_value">text</ns1:node_name>
 *    @usage
 *      new XmlElement('node_name', array('attr_name' => 'attr_value'), 'text', 'ns1');
 *
 * 2. Пустой элемент:
 *    @example
 *      <node_name/>
 *    @usage
 *      new XmlElement('node_name', array(), null);
 *
 * 3. Список вложенных нодов:
 *    @example
 *      <node_wrapper>
 *          <node_name>1</node_name>
 *          <node_name>2</node_name>
 *          <node_name>3</node_name>
 *      </node_wrapper>
 *    @usage
 *      new XmlElement('node_wrapper', array(), array(
 *          new XmlElement('node_name', array(), '1')),
 *          new XmlElement('node_name', array(), '2')),
 *          new XmlElement('node_name', array(), '3'))
 *      );
 *
 * 4. Список нодов без вложенности:
 *    @example
 *      <node_name>1</node_name>
 *      <node_name>2</node_name>
 *      <node_name>3</node_name>
 *    @usage
 *      new XmlElement(null, array(), array(
 *          new XmlElement('node_name', array(), '1')),
 *          new XmlElement('node_name', array(), '2')),
 *          new XmlElement('node_name', array(), '3'))
 *      );
 *
 * @package ReservationKit\src\Component\XML
 */
class XmlElement
{
    private $_nodeName;

    private $_attributes;

    private $_data = false;

    private $_ns;

    private $_isCreateEmpty;

    /**
     * @param string $nodeName Название нода
     * @param array|null $attributes Атрибуты
     * @param string|array|XmlElement $data Содержимое нода
     * @param string $ns Пространство имен нода
     * @param bool $isCreateEmpty Флаг создавать нод с пкстым содержимым или нет
     */
    public function __construct($nodeName, $attributes = array(), $data = null, $ns = null, $isCreateEmpty = true)
    {
        if (is_array($data) && !empty($data)) {
            foreach ($data as $element) {
                if ($element instanceof XmlElement) {
                    $this->addChild($element);
                }
            }

        } else if ($data instanceof XmlElement) {
            $this->addChild($data);

        } else if (is_string($data) || is_numeric($data) || is_null($data)) {
            $this->_data = (string) $data;

        } else {
            //throw new \RK_Core_Exception('Type ' . gettype($data) . ' does not support for XmlElement');
        }

        $this->_nodeName      = (string) $nodeName;
        $this->_attributes    = $attributes;
        $this->_ns            = $ns;
        $this->_isCreateEmpty = $isCreateEmpty;
    }

    public function addChild(XmlElement $element)
    {
        if (empty($this->_data)) {
            $this->_data = array();
        }

        $this->_data[] = $element;
    }

    public function getXml()
    {
        $_nodeContent   = $this->_data;
        $_isCreateEmpty = $this->_isCreateEmpty;

        if (empty($this->_nodeName) && empty($_nodeContent)) {
            return '';

        } else if (!$_isCreateEmpty && empty($_nodeContent) && (string) $_nodeContent !== '0') {
            return '';

        } else if (is_array($this->_data)) {
            $_nodeContent = '';

            // Получение списка xml-нодов из массива элементов XmlElement
            /* @var XmlElement $xmlElement */
            foreach ($this->_data as $xmlElement) {
                $_nodeContent .= $xmlElement->getXml();
            }

            // Если вложенный XmlElement пуст
            if (!$_isCreateEmpty && empty($_nodeContent) && (string) $_nodeContent !== '0') {
                return '';
            }

            // Возвращает список xml-нодов без вложенности
            if (empty($this->_nodeName)) {
                return $_nodeContent;
            }

        }

        // Генерация xml-нода
        return Generator::createXMLElement($this->_nodeName, $this->_attributes, $_nodeContent, $this->_ns);
    }

    public function getSimpleXmlElement()
    {

    }

    public function getAttributes()
    {
		return $this->_attributes;
    }
	
	public function getData()
	{
		return $this->_data;
	}
	
}