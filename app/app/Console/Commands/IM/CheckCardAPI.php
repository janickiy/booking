<?php

namespace App\Console\Commands\IM;

use Illuminate\Console\Command;
use App\Services\External\InnovateMobility\v1\Card;
use App\Models\References\StatusApi;
use Carbon\Carbon;

class CheckCardAPI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'services:im:checkCardAPI';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Тестирование работоспособности IM API Railway/V1/Card';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $options = [];

        $result = Card::getPricing(
            $options, true,
            ['allowZeroPlace' => 0]
        );

        if ($result) {
            self::setStatus('railway.v1.card.pricing', true);

            $this->info('Сервис Railway доступен!');
        } else {
            self::setStatus('railway.v1.card.pricing', false, 'Сервис Railway доступен!');

            $this->error('Сервис Railway доступен!');
        }
    }

    /**
     * @param $api
     * @param $status
     * @param null $message
     */
    private static function setStatus($api, $status, $message = null)
    {
        if (StatusApi::where('api_name', $api)->count() > 0) {
            $statusApi = StatusApi::where('api_name', $api)->first();
            $statusApi->message = $message;
            $statusApi->status = $status;
            $statusApi->checkAt = Carbon::now();
            $statusApi->updated_at = Carbon::now();
            $statusApi->update();

        } else {
            $statusApi = new StatusApi;
            $statusApi->api_name = $api;
            $statusApi->message = $message;
            $statusApi->status = $status;
            $statusApi->checkAt = Carbon::now();
            $statusApi->created_at = Carbon::now();
            $statusApi->save();
        }
    }
}
