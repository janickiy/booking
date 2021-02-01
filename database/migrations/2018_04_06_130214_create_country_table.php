<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCountryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('pgsql')->create('country', function (Blueprint $table) {
            $table->increments('countryId');
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
        Schema::connection('pgsql')->dropIfExists('country');
    }
}
