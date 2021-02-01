<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersRailwayTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('pgsql')->create('orders_railway', function (Blueprint $table) {
            $table->increments('orderId');
            $table->integer('userId')->default(0);
            $table->integer('holdingId')->default(0);
            $table->integer('clientId')->default(0);
            $table->integer('complexOrderId')->default(0);
            $table->integer('orderStatus')->default(0);
            $table->json('passengersData')->default('{}');
            $table->json('paymentsData')->default('{}');
            $table->json('orderData')->default('{}');
            $table->json('orderDocuments')->default('{}');
            $table->string('fromIp');
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
        Schema::connection('pgsql')->dropIfExists('orders_railway');
    }
}
