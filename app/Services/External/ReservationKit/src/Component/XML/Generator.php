<?php

namespace ReservationKit\src\Component\XML;

use ReservationKit\src\Component\XML\Exception\GeneratorException;

class Generator
{
    /**
     * Создает строковый XML element (node)
     *
     * @param string $node Название нода
     * @param array $attributes Атрибуты
     * @param string $data Содержимое нода
     * @param string $namespace Namespace нода
     * @return string
     */
    public static function createXMLElement($node, $attributes = array(), $data = null, $namespace = null)
    {
        // Пространство имен
        if ($namespace !== null) {
            $node = $namespace . ':' . $node;
        }

        // Атрибуты
        if (is_array($attributes)) {
            $xmlAttributes = '';
            foreach ($attributes as $key => $value) {
                $xmlAttributes .= ' ' . $key . '="' . $value . '"';
            }
        }

        // Нод
        if (empty($data) && $data !== '0') {
            $node = '<' . $node . $xmlAttributes . '/>';
        } else if (is_string($data)) {
            $node = '<' . $node . $xmlAttributes . '>' . $data . '</' . $node . '>';
        } else {
            throw new GeneratorException('Неверный тип содержимого нода');
        }

        return $node;
    }
}