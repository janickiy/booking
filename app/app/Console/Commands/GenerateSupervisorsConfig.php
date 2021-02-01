<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 17.01.2019
 * Time: 10:22
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateSupervisorsConfig extends Command
{
    protected $signature = 'services:supervisor';
    protected $description = 'Генерация конфигов для Supervisor';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $workers = config('trivago.workers.queues', []);
        $queueNames = [];

        $this->info('Build config data...');

        foreach ($workers as $name => $settings){
            for($pullNumber=0;$pullNumber <= $settings['workersNumber'];$pullNumber++){
                $queueNames[]=$name.'_'.$pullNumber;
            }
        }

        $app_dir = base_path();
        $user = config('trivago.workers.user');

        $config='';
        foreach ($queueNames as $pname){
            $config .= "[program:{$pname}]".PHP_EOL;
            $config .= "command=php {$app_dir}/artisan queue:work --queue={$pname} --tries=2".PHP_EOL;
            $config .= "user={$user}".PHP_EOL;
            $config .= "priority=999".PHP_EOL;
            $config .= "autostart=true".PHP_EOL;
            $config .= "autorestart=true".PHP_EOL;
            $config .= PHP_EOL;
        }

        file_put_contents(storage_path().'/trivago.conf', $config);
        $this->info('Writing config file...');
    }
}