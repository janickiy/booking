<?php

use Illuminate\Database\Seeder;

class LanguagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('ltm_languages')->insert([
            [
                'name' => 'Русский',
                'locale' => 'ru',
                'hide' => 0,
            ],
            [
                'name' => 'English',
                'locale' => 'en',
                'hide' => 0,
            ],
        ]);
    }
}
