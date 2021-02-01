<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsaddedmanuallyToTrainsCarTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trains_car', function (Blueprint $table) {
            $table->boolean('isAddedManually')->default(0)->after('train_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trains_car', function (Blueprint $table) {
            $table->dropColumn('isAddedManually');
        });
    }
}
