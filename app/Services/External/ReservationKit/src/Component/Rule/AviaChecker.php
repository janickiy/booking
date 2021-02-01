<?php

namespace ReservationKit\src\Component\Rule;

class AgreementChecker extends BaseChecker
{
    public function __construct()
    {

    }

    public function isApply($rule) {
        $result = true;

        $result = $result && $this->checkSegments($rule);

        return $result;
    }

    private function checkSegments($rule)
    {
        return true;
    }
}