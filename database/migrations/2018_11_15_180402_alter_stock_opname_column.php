<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AlterStockOpnameColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('point_inventory_stock_opname_item', function (Blueprint $table) {
            $table->decimal('cogs_in_database', 65, 30)->default(0);
            $table->decimal('cogs', 65, 30)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('point_inventory_stock_opname_item', function (Blueprint $table) {
            $table->dropColumn('cogs_in_database');
            $table->dropColumn('cogs');
        });
    }
}
