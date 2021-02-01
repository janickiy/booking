<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReferencesTablesIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('pgsql')->table('country', function (Blueprint $table) {
            $table->index('sourceId', $table->getTable() . '_sourceId');
            $table->index('code', $table->getTable() . '_code');
            $table->index('source', $table->getTable() . '_source');
            $table->index('isActive', $table->getTable() . '_isActive');
            $table->index(['nameRu', 'nameEn'], $table->getTable() . '_names');
        });

        Schema::connection('pgsql')->table('region', function (Blueprint $table) {
            $table->index('sourceId', $table->getTable() . '_sourceId');
            $table->index('countryId', $table->getTable() . '_countryId');
            $table->index('code', $table->getTable() . '_code');
            $table->index('source', $table->getTable() . '_source');
            $table->index('isActive', $table->getTable() . '_isActive');
            $table->index(['nameRu', 'nameEn'], $table->getTable() . '_names');
        });

        Schema::connection('pgsql')->table('city', function (Blueprint $table) {
            $table->index('sourceId', $table->getTable() . '_sourceId');
            $table->index('countryId', $table->getTable() . '_countryId');
            $table->index('regionId', $table->getTable() . '_regionId');
            $table->index('code', $table->getTable() . '_code');
            $table->index('source', $table->getTable() . '_source');
            $table->index('isActive', $table->getTable() . '_isActive');
            $table->index(['nameRu', 'nameEn'], $table->getTable() . '_names');
        });

        Schema::connection('pgsql')->table('railway_station', function (Blueprint $table) {
            $table->index('sourceId', $table->getTable() . '_sourceId');
            $table->index('countryId', $table->getTable() . '_countryId');
            $table->index('regionId', $table->getTable() . '_regionId');
            $table->index('cityId', $table->getTable() . '_cityId');
            $table->index('code', $table->getTable() . '_code');
            $table->index('source', $table->getTable() . '_source');
            $table->index('isActive', $table->getTable() . '_isActive');
            $table->index(['nameRu', 'nameEn'], $table->getTable() . '_names');
        });

        Schema::connection('pgsql')->table('airport', function (Blueprint $table) {
            $table->index('sourceId', $table->getTable() . '_sourceId');
            $table->index('countryId', $table->getTable() . '_countryId');
            $table->index('regionId', $table->getTable() . '_regionId');
            $table->index('cityId', $table->getTable() . '_cityId');
            $table->index('code', $table->getTable() . '_code');
            $table->index('source', $table->getTable() . '_source');
            $table->index('isActive', $table->getTable() . '_isActive');
            $table->index(['nameRu', 'nameEn'], $table->getTable() . '_names');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('pgsql')->table('country', function (Blueprint $table) {
            $table->dropIndex( $table->getTable() . '_sourceId');
            $table->dropIndex( $table->getTable() . '_code');
            $table->dropIndex($table->getTable() . '_source');
            $table->dropIndex($table->getTable() . '_isActive');
            $table->dropIndex($table->getTable() . '_names');
        });

        Schema::connection('pgsql')->table('region', function (Blueprint $table) {
            $table->dropIndex( $table->getTable() . '_sourceId');
            $table->dropIndex( $table->getTable() . '_countryId');
            $table->dropIndex( $table->getTable() . '_code');
            $table->dropIndex( $table->getTable() . '_source');
            $table->dropIndex( $table->getTable() . '_isActive');
            $table->dropIndex( $table->getTable() . '_names');
        });

        Schema::connection('pgsql')->table('city', function (Blueprint $table) {
            $table->dropIndex( $table->getTable() . '_sourceId');
            $table->dropIndex( $table->getTable() . '_countryId');
            $table->dropIndex( $table->getTable() . '_regionId');
            $table->dropIndex( $table->getTable() . '_code');
            $table->dropIndex( $table->getTable() . '_source');
            $table->dropIndex( $table->getTable() . '_isActive');
            $table->dropIndex( $table->getTable() . '_names');
        });

        Schema::connection('pgsql')->table('railway_station', function (Blueprint $table) {
            $table->dropIndex( $table->getTable() . '_sourceId');
            $table->dropIndex( $table->getTable() . '_countryId');
            $table->dropIndex( $table->getTable() . '_regionId');
            $table->dropIndex( $table->getTable() . '_cityId');
            $table->dropIndex( $table->getTable() . '_code');
            $table->dropIndex( $table->getTable() . '_source');
            $table->dropIndex( $table->getTable() . '_isActive');
            $table->dropIndex( $table->getTable() . '_names');
        });

        Schema::connection('pgsql')->table('airport', function (Blueprint $table) {
            $table->dropIndex( $table->getTable() . '_sourceId');
            $table->dropIndex( $table->getTable() . '_countryId');
            $table->dropIndex( $table->getTable() . '_regionId');
            $table->dropIndex( $table->getTable() . '_cityId');
            $table->dropIndex( $table->getTable() . '_code');
            $table->dropIndex( $table->getTable() . '_source');
            $table->dropIndex( $table->getTable() . '_isActive');
            $table->dropIndex( $table->getTable() . '_names');
        });
    }
}
