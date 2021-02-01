<?php

namespace ReservationKit\src\Modules\Galileo\Model\RequestParam;

use ReservationKit\src\Component\XML\XmlElement;

class BrandText extends XmlElement
{
    /**
     * @param string $content Сожержимое элемента Text
     * @param array|null $type Атрибут Type у элемента Text
     */
    public function __construct($content, $type)
    {
        $Text = new XmlElement('Text', array('Type' => $type), $content, 'air');

        parent::__construct(null, array(), $Text);
    }
}