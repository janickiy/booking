<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->increments('id');
            $table->string('titleRu');
            $table->string('titleEn')->nullable();
            $table->text('contentRu')->nullable();
            $table->text('contentEn')->nullable();
            $table->string('meta_titleRu')->nullable();
            $table->string('meta_descriptionRu')->nullable();
            $table->string('meta_keywordsRu')->nullable();
            $table->string('meta_titleEn')->nullable();
            $table->string('meta_descriptionEn')->nullable();
            $table->string('meta_keywordsEn')->nullable();
            $table->string('slug')->unique();
            $table->boolean('published')->default(1);
            $table->integer('parent_id')->default(0)->nullable();
            $table->boolean('page_path')->default(1);
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
        Schema::dropIfExists('pages');
    }
}
