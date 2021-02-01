<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePassengersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('pgsql')->create('passengers', function (Blueprint $table) {
            $table->increments('passengerId');
            $table->integer('userId')->default(0);
            $table->integer('holdingId')->default(0);
            $table->integer('clientId')->default(0);
            $table->string('firstName');
            $table->string('middleName');
            $table->string('lastName');
            $table->json('contacts')->default('{}');
            $table->json('documents')->default('{}');
            $table->json('cards')->default('{}');
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
        Schema::connection('pgsql')->dropIfExists('passengers');
    }
}
