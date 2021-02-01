<?php

namespace App\Console\Commands\IM;

use App\Http\Controllers\TranslationManager\Manager;
use App\Models\References\{Trains};
use App\Helpers\StringHelpers;
use Illuminate\Console\Command;

class UpdateTrainsTranslations extends Command
{
    protected $manager;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'services:im:updatetrainstranslations
    {sections* : Обновить указанные секции (all,description,carriers)}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Обновление базы языкового модуля строк для перевода и правильного отображения информации по поездам';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $trains = Trains::select('trainNumber', 'trainDescription', 'carriers')->get();

        $add = 0;
        // $update = 0;

        foreach ($trains as $train) {
            if (in_array('all', $this->argument('sections')) || in_array('description', $this->argument('sections'))) {
                if ($train->trainDescription && $train->trainNumber) {
                    //   $replace = $this->manager->isExistTranslation(StringHelpers::slug($train->trainDescription), 'ru', 'railway/trainDescription');
                    $result = $this->manager->importTranslation(StringHelpers::slug($train->trainDescription), $train->trainDescription, 'ru', 'railway/trainDescription');

                    if ($result) $add++;

                    //  $replace = $this->manager->isExistTranslation(StringHelpers::slug($train->trainDescription), 'en', 'railway/trainDescription');
                    $result = $this->manager->importTranslation(StringHelpers::slug($train->trainDescription), $train->trainDescription, 'en', 'railway/trainDescription');

                    if ($result) $add++;
                }
            }

            if (in_array('all', $this->argument('sections')) || in_array('carriers', $this->argument('sections'))) {
                if ($train->carriers && $train->trainNumber) {
                    foreach ($train->carriers as $carrier) {
                        // $replace = $this->manager->isExistTranslation(StringHelpers::slug($carrier), 'ru', 'railway/carriers');
                        $result = $this->manager->importTranslation(StringHelpers::slug($carrier), $carrier, 'ru', 'railway/carriers');

                        if ($result) $add++;

                        //  $replace = $this->manager->isExistTranslation(StringHelpers::slug($carrier), 'en', 'railway/carriers');
                        $result = $this->manager->importTranslation(StringHelpers::slug($carrier), $carrier, 'en', 'railway/carriers');

                        if ($result) $add++;
                    }
                }
            }
        }

        // $this->info('Обновлено описаний: ' . $update);
        $this->info('Добавлено: ' . $add);
    }
}
