<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('OrderMessages', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id')->index('order_id');
            $table->string('order_type');
            $table->integer('order_item_id');
            $table->integer('sender_id')->index('sender_id')->comment('id отправителя');
            $table->integer('receiver_id')->index('receiver_id')->comment('id получателя');
            $table->text('message');
            $table->tinyInteger('status')->comment('2 - удалeно у отправителя 3 - удалено у получателя 4 - удалено у обоих');
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
        Schema::dropIfExists('OrderMessages');
    }
}
