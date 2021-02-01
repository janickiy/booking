<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSessionLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('log')->create('session_log', function (Blueprint $table) {

            $table->increments('session_log_id');
            $table->string('session_id');
            $table->integer('user_id')->default(0);
            $table->string('referer');
            $table->string('path');
            $table->string('route');
            $table->json('request');
            $table->json('response');
            $table->json('external');
            $table->json('queries');
            $table->float('log_start_time');
            $table->float('log_end_time');
            $table->timestamps();

            $table->index(['session_id']);
            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('log')->dropIfExists('session_log');
    }
}
