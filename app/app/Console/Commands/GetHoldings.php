<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GetHoldings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'services:get-holdings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Получаем список холдингов со старого сайта';

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
        \App\Models\Holding::truncate();

        $holdings = \App\Models\Old\Holding::all();

        foreach ($holdings as $holding){

            \App\Models\Holding::create([
                'holdingId'=>$holding->id,
                'name'=>$holding->name
            ]);
        }
    }
}
