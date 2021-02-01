<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 20.03.2018
 * Time: 13:41
 */

namespace App\Services;


use App\Models\QueuedJob;

class QueueBalanced
{
    /**
     * @param $job
     * @param $pullName
     * @return integer
     */
    static public function balance($job, $pullName)
    {
        $workers = config('trivago.workers.queues', false);

        if(!$workers || !isset($workers[$pullName])){
            $job->onQueue('default');
            return app(\Illuminate\Contracts\Bus\Dispatcher::class)->dispatch($job);
        }

        $queueNames = [];

        for($pullNumber=0;$pullNumber <= $workers[$pullName]['workersNumber'];$pullNumber++){
            $queueNames[]=$pullName.'_'.$pullNumber;
        }

        $jobsQueues = QueuedJob::select(['queue',\DB::raw('count(*) as qty')])->whereIn('queue',$queueNames)->groupBy('queue')->get();

        if($jobsQueues->count()==0){
            $job->onQueue($queueNames[0]);
        }elseif($jobsQueues->count() < $workers[$pullName]['workersNumber']){
            $lastQueue = $jobsQueues->last();
            $lastIndex = explode('_',$lastQueue->queue)[1];
            if(($lastIndex +1 ) <= $workers[$pullName]['workersNumber']) {
                $job->onQueue($queueNames[($lastIndex + 1)]);
            }else{
                $job->onQueue($queueNames[0]);
            }
        }else{
            $jobsQueues = $jobsQueues->sortBy('qty');
            $freeQueue = $jobsQueues->first();
            $lastIndex = explode('_',$freeQueue->queue)[1];
            $job->onQueue($queueNames[($lastIndex)]);
        }
        return app(\Illuminate\Contracts\Bus\Dispatcher::class)->dispatch($job);
    }

}