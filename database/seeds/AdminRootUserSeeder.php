<?php

use Illuminate\Database\Seeder;
use App\Models\Admin\AdminUser;

class AdminRootUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AdminUser::create([
            'email' => 'guchenko@trivago.ru',
            'login' => 'root' ,
            'name' => 'Superuser',
            'password' => app('hash')->make('SoulDamnScaringLollipop18'),
            'allowedIp' => ['192.168.*']
        ]);
    }
}
