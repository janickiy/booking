<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('pgsql')->create('region', function (Blueprint $table) {
            $table->increments('regionId');
            $table->integer('countryId');
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
        Schema::connection('pgsql')->dropIfExists('region');
    }
}
