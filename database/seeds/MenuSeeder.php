<?php

use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::connection('pgsql')->table('menu')->truncate();

        \DB::table('menu')->insert([
            [
                'titleRu' => 'Верхнее меню',
                'url' => '',
                'status' => 1,
                'item_order' => 1,
                'parent_id' => 0,
                'menu_type' => 'url',
                'item_id' => null,
            ],
            [
                'titleRu' => 'Нижнее меню',
                'url' => '',
                'status' => 1,
                'item_order' => 1,
                'parent_id' => 0,
                'menu_type' => 'url',
                'item_id' => null,
            ],
            [
                'titleRu' => 'Авиабилеты',
                'url' => '',
                'status' => 1,
                'item_order' => 1,
                'parent_id' => 0,
                'menu_type' => 'url',
                'item_id' => null,
            ],
            [
                'titleRu' => 'Ж/Д билеты',
                'url' => '',
                'status' => 1,
                'item_order' => 1,
                'parent_id' => 0,
                'menu_type' => 'url',
                'item_id' => null,
            ],
            [
                'titleRu' => 'Услуги',
                'url' => '',
                'status' => 1,
                'item_order' => 1,
                'parent_id' => 0,
                'menu_type' => 'url',
                'item_id' => null,
            ],
            [
                'titleRu' => 'Пассажирам',
                'url' => '',
                'status' => 1,
                'item_order' => 1,
                'parent_id' => 0,
                'menu_type' => 'url',
                'item_id' => null,
            ],
            [
                'titleRu' => 'Мы в соцсетях',
                'url' => '',
                'status' => 1,
                'item_order' => 1,
                'parent_id' => 0,
                'menu_type' => 'url',
                'item_id' => null,
            ],
            [
                'titleRu' => 'Трансфер',
                'url' => 'transfer',
                'status' => 1,
                'item_order' => 2,
                'parent_id' => 1,
                'menu_type' => 'url',
                'item_id' => null,
            ],
            [
                'titleRu' => 'Страховка',
                'url' => 'insurance',
                'status' => 1,
                'item_order' => 6,
                'parent_id' => 1,
                'menu_type' => 'url',
                'item_id' => null,
            ],
            [
                'titleRu' => 'MICE',
                'url' => 'mice',
                'status' => 1,
                'item_order' => 7,
                'parent_id' => 1,
                'menu_type' => 'url',
                'item_id' => null,
            ],
            [
                'titleRu' => 'Отели',
                'url' => 'hotels',
                'status' => 1,
                'item_order' => 4,
                'parent_id' => 1,
                'menu_type' => 'url',
                'item_id' => null,
            ],
            [
                'titleRu' => 'Авто',
                'url' => 'auto',
                'status' => 1,
                'item_order' => 5,
                'parent_id' => 1,
                'menu_type' => 'url',
                'item_id' => null,
            ],
            [
                'titleRu' => 'Поезда',
                'url' => 'trains',
                'status' => 1,
                'item_order' => 3,
                'parent_id' => 1,
                'menu_type' => 'url',
                'item_id' => null,
            ],
            [
                'titleRu' => 'Авиа',
                'url' => 'avia',
                'status' => 1,
                'item_order' => 1,
                'parent_id' => 1,
                'menu_type' => 'url',
                'item_id' => null,
            ],
            [
                'titleRu' => 'Бронирование и покупка',
                'url' => '',
                'status' => 1,
                'item_order' => 1,
                'parent_id' => 3,
                'menu_type' => 'page',
                'item_id' => 3,
            ],
            [
                'titleRu' => 'АК и Аэропорты',
                'url' => '',
                'status' => 1,
                'item_order' => 2,
                'parent_id' => 3,
                'menu_type' => 'page',
                'item_id' => 4,
            ],
            [
                'titleRu' => 'Провоз багажа',
                'url' => '',
                'status' => 1,
                'item_order' => 3,
                'parent_id' => 3,
                'menu_type' => 'page',
                'item_id' => 5,
            ],
            [
                'titleRu' => 'Правила возврата',
                'url' => '',
                'status' => 1,
                'item_order' => 4,
                'parent_id' => 3,
                'menu_type' => 'page',
                'item_id' => 6,
            ],
            [
                'titleRu' => 'Правила оформления',
                'url' => '',
                'status' => 1,
                'item_order' => 1,
                'parent_id' => 4,
                'menu_type' => 'page',
                'item_id' => 8,
            ],
            [
                'titleRu' => 'Регистрация',
                'url' => '',
                'status' => 1,
                'item_order' => 2,
                'parent_id' => 4,
                'menu_type' => 'page',
                'item_id' => 9,
            ],
            [
                'titleRu' => 'Провоз ручной клади',
                'url' => '',
                'status' => 1,
                'item_order' => 3,
                'parent_id' => 4,
                'menu_type' => 'page',
                'item_id' => 10,
                ],
            [
                'titleRu' => 'Аэроэкспресс',
                'url' => '',
                'status' => 1,
                'item_order' => 0,
                'parent_id' => 5,
                'menu_type' => 'page',
                'item_id' => 13,
            ],
            [
                'titleRu' => 'Гостиницы',
                'url' => '',
                'status' => 1,
                'item_order' => 1,
                'parent_id' => 5,
                'menu_type' => 'page',
                'item_id' => 14,
            ],
            [
                'titleRu' => 'Прокат авто',
                'url' => '',
                'status' => 1,
                'item_order' => 2,
                'parent_id' => 5,
                'menu_type' => 'page',
                'item_id' => 15,
            ],
            [
                'titleRu' => 'Страхование',
                'url' => '',
                'status' => 1,
                'item_order' => 3,
                'parent_id' => 5,
                'menu_type' => 'page',
                'item_id' => 16,
            ],
            [
                'titleRu' => 'Страны',
                'url' => '',
                'status' => 1,
                'item_order' => 1,
                'parent_id' => 6,
                'menu_type' => 'page',
                'item_id' => 18,
            ],
            [
                'titleRu' => 'Города',
                'url' => '',
                'status' => 1,
                'item_order' => 2,
                'parent_id' => 6,
                'menu_type' => 'page',
                'item_id' => 19,
            ],
            [
                'titleRu' => 'Популярные маршруты',
                'url' => '',
                'status' => 1,
                'item_order' => 3,
                'parent_id' => 6,
                'menu_type' => 'page',
                'item_id' => 20,
            ],
            [
                'titleRu' => 'Спецпредложения',
                'url' => '',
                'status' => 1,
                'item_order' => 4,
                'parent_id' => 6,
                'menu_type' => 'page',
                'item_id' => 21,
            ],
            [
                'titleRu' => 'Factbook',
                'url' => '',
                'status' => 1,
                'item_order' => 1,
                'parent_id' => 7,
                'menu_type' => 'url',
                'item_id' => null,
            ],
            [
                'titleRu' => 'VKontakte',
                'url' => '',
                'status' => 1,
                'item_order' => 2,
                'parent_id' => 7,
                'menu_type' => 'url',
                'item_id' => null,
            ],
            [
                'titleRu' => 'Instagram',
                'url' => '',
                'status' => 1,
                'item_order' => 3,
                'parent_id' => 7,
                'menu_type' => 'url',
                'item_id' => null,
            ],
            [
                'titleRu' => 'О компании',
                'url' => '/',
                'status' => 1,
                'item_order' => 3,
                'parent_id' => 2,
                'menu_type' => 'url',
                'item_id' => null,
            ],
            [
                'titleRu' => 'Контакты',
                'url' => '/contacts',
                'status' => 1,
                'item_order' => 3,
                'parent_id' => 2,
                'menu_type' => 'url',
                'item_id' => null,
            ],
            [
                'titleRu' => 'Поддержка',
                'url' => '/support',
                'status' => 1,
                'item_order' => 3,
                'parent_id' => 2,
                'menu_type' => 'url',
                'item_id' => null,
            ]
        ]);
    }
}