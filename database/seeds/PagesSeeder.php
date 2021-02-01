<?php

use Illuminate\Database\Seeder;

class PagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::connection('pgsql')->table('pages')->truncate();

        \DB::table('pages')->insert([
            [
                'titleRu' => 'Авиабилеты',
                'slug' => 'aviabiletyi',
                'parent_id' => 0,
                'published' => 1,
                'page_path' => 0,
            ],
            [
                'titleRu' => 'Бронирование и покупка',
                'slug' => 'bronirovanie-i-pokupka',
                'parent_id' => 2,
                'published' => 1,
                'page_path' => 1,
            ],
            [
                'titleRu' => 'АК и Аэропорты',
                'slug' => 'ak-i-aeroportyi',
                'parent_id' => 2,
                'published' => 1,
                'page_path' => 1,
            ],
            [
                'titleRu' => 'Провоз багажа',
                'slug' => 'provoz-bagaja',
                'parent_id' => 2,
                'published' => 1,
                'page_path' => 1,
            ],
            [
                'titleRu' => 'Правила возврата',
                'slug' => 'pravila-vozvrata',
                'parent_id' => 2,
                'published' => 1,
                'page_path' => 1,
            ],
            [
                'titleRu' => 'Ж/Д билеты',
                'slug' => 'jd-biletyi',
                'parent_id' => 0,
                'published' => 1,
                'page_path' => 0,
            ],
            [
                'titleRu' => 'Правила оформления',
                'slug' => 'pravila-oformleniya',
                'parent_id' => 7,
                'published' => 1,
                'page_path' => 1,
            ],
            [
                'titleRu' => 'Регистрация',
                'slug' => 'registratsiya',
                'parent_id' => 7,
                'published' => 1,
                'page_path' => 1,
            ],
            [
                'titleRu' => 'Провоз ручной клади',
                'slug' => 'provoz-ruchnoy-kladi',
                'parent_id' => 7,
                'published' => 1,
                'page_path' => 1,
            ],
            [
                'titleRu' => 'Классификация вагонов',
                'slug' => 'klassifikatsiya-vagonov',
                'parent_id' => 7,
                'published' => 1,
                'page_path' => 1,
            ],
            [
                'titleRu' => 'Услуги',
                'slug' => 'uslugi',
                'parent_id' => 0,
                'published' => 1,
                'page_path' => 0,
            ],
            [
                'titleRu' => 'Аэроэкспресс',
                'slug' => 'aeroekspressi',
                'parent_id' => 12,
                'published' => 1,
                'page_path' => 1,
            ],
            [
                'titleRu' => 'Гостиницы',
                'slug' => 'gostinitsyi',
                'parent_id' => 12,
                'published' => 1,
                'page_path' => 1,
            ],
            [
                'titleRu' => 'Прокат авто',
                'slug' => 'prokat-avto',
                'parent_id' => 12,
                'published' => 1,
                'page_path' => 1,
            ],
            [
                'titleRu' => 'Пассажирам',
                'slug' => 'passajiram',
                'parent_id' => 0,
                'published' => 1,
                'page_path' => 0,
            ],
            [
                'titleRu' => 'Страны',
                'slug' => 'strany',
                'parent_id' => 17,
                'published' => 1,
                'page_path' => 1,
            ],
            [
                'titleRu' => 'Города',
                'slug' => 'goroda',
                'parent_id' => 17,
                'published' => 1,
                'page_path' => 1,
            ],
            [
                'titleRu' => 'Популярные маршруты',
                'slug' => 'populyarnyie-marshrutyi',
                'parent_id' => 17,
                'published' => 1,
                'page_path' => 1,
            ],
            [
                'titleRu' => 'Спецпредложения',
                'slug' => 'spetspredlojeniya',
                'parent_id' => 17,
                'published' => 1,
                'page_path' => 1,
            ],
        ]);
    }
}