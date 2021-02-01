<?php

namespace App\Contracts\Api;

interface ErrorCodesEnv
{
    const ERROR_CODE_DEFAULT = 1000;

    //Аэроэкспресс
    const ERROR_NOT_FOUND_RACE = 100;
    const ERROR_NO_TICKETS = 101;

}
