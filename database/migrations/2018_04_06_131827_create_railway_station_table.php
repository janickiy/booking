<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRailwayStationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('pgsql')->create('railway_station', function (Blueprint $table) {
            $table->increments('railwayStationId');
            $table->integer('countryId');
            $table->integer('regionId');
            $table->integer('cityId');
            $table->integer('sourceId');
            $table->string('code');
            $table->string('nameRu');
            $table->string('nameEn');
            $table->boolean('isActive');
            $table->dateTime('sourceUpdatedAt');
            $table->string('source');
            $table->json('info')->default('{}');
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
        Schema::connection('pgsql')->dropIfExists('railway_station');
    }
}
