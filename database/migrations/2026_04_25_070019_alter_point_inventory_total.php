<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AlterInventoryTotal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('alter_inventory_total', function (Blueprint $table) {
            $table->decimal('total_quantity_all', 16, 4)->default(0);
            $table->decimal('total_value_all', 16, 4)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('alter_inventory_total', function (Blueprint $table) {
            $table->dropColumn('point_sales_invoice_id');
        });
    }
}
