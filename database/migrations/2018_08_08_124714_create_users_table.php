<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('pgsql')->create('users', function (Blueprint $table) {
            $table->increments('userId');
            $table->integer('userTypeId')->default(0);
            $table->integer('holdingId')->default(0);
            $table->integer('clientId')->default(0);
            $table->string('email');
            $table->string('login');
            $table->string('password');
            $table->json('contacts')->default('{}');
            $table->string('lastAccessIp')->default('');
            $table->json('allowedIp')->default('[]');
            $table->dateTime('last_activity_at')->default('1970-01-01 00:00:00');
            $table->dateTime('last_login_at')->default('1970-01-01 00:00:00');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('pgsql')->dropIfExists('users');
    }
}
