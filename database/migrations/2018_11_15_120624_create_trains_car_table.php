<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrainsCarTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trains_car', function (Blueprint $table) {
            $table->increments('id');

            $table->string('typeRu');
            $table->string('typeEn');
            $table->string('description')->nullable();
            $table->string('typeScheme');
            $table->string('scheme')->nullable();
            $table->integer('train_id');
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
        Schema::dropIfExists('trains_car');
    }
}
