<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 26.04.2019
 * Time: 18:09
 */

namespace App\Libraries;


use App\Services\QueueBalanced;
use Trivago\Hotels\Modules\Jobs\Dispatcher;

class JobsDispatcher implements Dispatcher
{

    public function dispatch($job, string $queue)
    {
        QueueBalanced::balance($job, $queue);
    }
}