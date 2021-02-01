<?php

namespace ReservationKit\src\Modules\Avia\Model\Abstracts;

abstract class RequisiteRulesAbstract
{
    abstract public function toJson();

    abstract public function fillFromJson($jsonData);
}