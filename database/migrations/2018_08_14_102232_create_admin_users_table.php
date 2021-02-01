<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('pgsql')->create('admin_users', function (Blueprint $table) {
            $table->increments('adminUserId');
            $table->string('email');
            $table->string('login');
            $table->string('name');
            $table->string('password');
            $table->string('remember_token')->default('');
            $table->string('lastAccessIp')->default('');
            $table->dateTime('last_activity_at')->default('now()');
            $table->dateTime('last_login_at')->default('now()');
            $table->json('allowedIp')->default('[]');
            $table->timestamps();
        });

        Artisan::call('db:seed', [
            '--class' => AdminRootUserSeeder::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('pgsql')->dropIfExists('admin_users');
    }
}
