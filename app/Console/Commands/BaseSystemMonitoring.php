<?php
/**
 * Created by PhpStorm.
 * User: guchenko
 * Date: 21.02.2019
 * Time: 11:22
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BaseSystemMonitoring extends Command
{

    protected $signature = 'services:monitoring';
    protected $description = 'Генерация конфигов для Supervisor';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        app('sentry')->captureMessage('Schedule works :)');
    }
}