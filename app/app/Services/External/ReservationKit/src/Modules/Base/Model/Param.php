<?php

class RK_Base_Param
{
    /**
     * @var array
     */
    private $_params = array();

    /**
     * @return string
     */
    //abstract public function getXML();

    /**
     * Создание SimpleXMLElement-документа
     *
     * @param string $node Название нода
     * @param string $text Текст нода
     * @param array $attributes Атрибуты
     * @param string $namespace Namespace нода
     * @return SimpleXMLElement
     */
    public function createXMLElement($node, $text = null, array $attributes = array(), $namespace = null)
    {
        // Пространство имен
        if ($namespace !== null) {
            $node = $namespace . ':' . $node;
        }

        // Атрибуты
        $xmlAttributes = '';
        foreach ($attributes as $key => $value) {
            $xmlAttributes .= ' ' . $key . '="' . $value . '"';
        }

        // Нод
        if (empty($text)) {
            $node = '<' . $node . $xmlAttributes . '/>';
        } else {
            $node = '<' . $node . $xmlAttributes . '>' . $text . '</' . $node . '>';
        }

        return $node;
    }

    /**
     * Добавляет параметр в массив параметров
     *
     * @param string $key Ключ (название) параметра
     * @param mixed $value Значение параметра
     */
    protected function addParam($key, $value)
    {
        $this->_params[$key] = $value;
    }

    /**
     * Возвращает параметр по ключу
     *
     * @param string $key Ключ (название) параметра
     * @return mixed
     */
    protected function getParam($key)
    {
        return isset($this->_params[$key]) ? $this->_params[$key] : null;
    }

    /**
     * Проверка обязательных параметров в массиве
     *
     * Проверяется наличие ключей и соответствие типу обязательного параметра. Если тип null или '', то проверки типа нет.
     * Тип должен проверяться на значения: integer, double, string, array, имя_класса.
     * Пример:
     *   $needParams = array(
     *      '<проверяемый_ключ>' => '<проверяемый_тип_соответствующего_значения>',
     *      'session' => 'RK_Sabre_Entity_Session',
     *      'id' => 'integer',
     *      'attributes' => null
     *   );
     *
     * @param array $params Массив параметров для проверки
     * @param array $needParams Массив необходимых параметров
     * @return bool
     * @throws RK_Sabre_Exception_Param
     */
    protected function checkRequireParams(array $params, array $needParams)
    {
        foreach ($needParams as $needKey => $needType) {
            // Проверка наличия ключа
            if (!isset($params[$needKey])) {
                throw new RK_Sabre_Exception_Param('Need set parameter "' . $needKey . '" for header request');
            }

            // Проверка типа
            if (!empty($needType)) {
                $checkType = is_object($params[$needKey]) ? get_class($params[$needKey]) : gettype($params[$needKey]);

                if ($needType !== $checkType) {
                    throw new RK_Sabre_Exception_Param('Wrong type "' . $checkType . '" set for "' . $needKey . '". Need type "' . $needType . '"');
                }
            }
        }

        return true;
    }
}