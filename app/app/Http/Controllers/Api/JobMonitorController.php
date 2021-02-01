<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 20.03.2018
 * Time: 16:57
 */

namespace App\Http\Controllers\Api;


use App\Helpers\ResponseHelpers;
use App\Models\QueuedJob;

/**
 * Class JobMonitorController
 * @group Jobs Monitoring
 * @package App\Http\Controllers\Api
 */
class JobMonitorController
{

    /**
     * Job progress
     * [Получение процента выполнения задачи в очереди]
     * @queryParam id required Id задачи
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function progress($id)
    {
        $job = QueuedJob::find($id);

        if(!$job){
            return ResponseHelpers::jsonResponse(['status'=>'done', 'progress'=>100]);
        }

        return ResponseHelpers::jsonResponse(['status'=>'pending', 'progress'=>$job->progress]);
    }

}