<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdminRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('pgsql')->create('admin_roles', function (Blueprint $table) {
            $table->increments('adminRoleId');
            $table->integer('accessLevel')->default(0);
            $table->json('accessMap')->default('{}');
            $table->timestamps();
        });

        Schema::connection('pgsql')->create('admin_user_roles', function (Blueprint $table) {
            $table->increments('adminUserRoleId');
            $table->integer('adminUserId');
            $table->integer('adminRoleId');
            $table->date('validTill')->default("now()");
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
        Schema::connection('pgsql')->dropIfExists('admin_roles');
        Schema::connection('pgsql')->dropIfExists('admin_user_roles');
    }
}
