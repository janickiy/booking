<?php

namespace App\Console\Commands;

use App\Models\ClientDepartments;
use App\Models\Clients;
use App\Models\Old\ClientsInHolding;
use App\Services\External\Rest1C\v1\ClientsServices;
use Illuminate\Console\Command;

class Rest1CGetClients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'services:1c-get-clients';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Получение справочника контрагентов из 1с';

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

        $lastUpdateDate = Clients::where('clientId', '!=', 0)->max('sourceUpdatedAt');
        $lastUpdateRequestDate = $lastUpdateDate ? date('Y-m-d', strtotime($lastUpdateDate)) : null;

        $todayClients = ClientsServices::getClients(['clientId' => null, 'lastUpdate' => $lastUpdateRequestDate]);

        if ($todayClients) {

            $bar = count($todayClients->clients) > 0 ? $this->output->createProgressBar(count($todayClients->clients)) : false;

            $clientsUpdate = 0;
            $clientsAdd = 0;

            foreach ($todayClients->clients as $client) {

                $clientModel = Clients::where('outerClientId', $client->clientId)->first();


                if (!$clientModel) {
                    $clientModel = new Clients();
                    $clientsAdd++;
                } else {
                    $clientsUpdate++;
                }

                $clientModel->fillFrom1C($client);
                $clientModel->save();

                $clientModel->updateDepartments($client->departments);

                $bar->advance();
            }

            if ($bar) {
                $bar->finish();
                $this->line('');
                $this->info('Контрагентов добавлено: ' . $clientsAdd);
                $this->info('Контрагентов обновлено: ' . $clientsUpdate);
            }

        } else {
            $this->info(ClientsServices::getLastError()['message']);
        }
    }
}
