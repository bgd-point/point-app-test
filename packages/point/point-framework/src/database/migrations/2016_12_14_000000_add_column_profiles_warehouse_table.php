<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnProfilesWarehouseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasColumn('warehouse', 'store_name')) {
            Schema::table('warehouse', function ($table) {
                $table->string('store_name');
            });
        }
        if (! Schema::hasColumn('warehouse', 'address')) {
            Schema::table('warehouse', function ($table) {
                $table->string('address');
            });
        }
        if (! Schema::hasColumn('warehouse', 'phone')) {
            Schema::table('warehouse', function ($table) {
                $table->string('phone');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('warehouse', 'store_name')) {
            Schema::table('warehouse', function ($table) {
                $table->dropColumn('store_name');
            });
        }
        if (Schema::hasColumn('warehouse', 'address')) {
            Schema::table('warehouse', function ($table) {
                $table->dropColumn('address');
            });
        }
        if (Schema::hasColumn('warehouse', 'phone')) {
            Schema::table('warehouse', function ($table) {
                $table->dropColumn('phone');
            });
        }
    }
}
