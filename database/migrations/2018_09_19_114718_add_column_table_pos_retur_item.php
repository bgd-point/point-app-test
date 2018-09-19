<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnTablePosReturItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('point_sales_pos_retur_item', function ($table) {
            $table->decimal('add_stock', 16, 4);
            $table->decimal('not_add_stock', 16, 4);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('point_sales_pos_retur_item', function ($table) {
            $table->dropColumn(['add_stock', 'not_add_stock']);
        });
    }
}
