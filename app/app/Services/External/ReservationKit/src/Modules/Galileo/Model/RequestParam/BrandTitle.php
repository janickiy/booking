<?php

namespace ReservationKit\src\Modules\Galileo\Model\RequestParam;

use ReservationKit\src\Component\XML\XmlElement;

class BrandTitle extends XmlElement
{
    /**
     * @param string $content Сожержимое элемента Title
     * @param array|null $type Атрибут Type у элемента Title
     */
    public function __construct($content, $type)
    {
        $Title = new XmlElement('Title', array('Type' => $type), $content, 'air');

        parent::__construct(null, array(), $Title);
    }
}