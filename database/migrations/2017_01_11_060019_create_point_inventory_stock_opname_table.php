<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointInventoryStockOpnameTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_inventory_stock_opname', function ($table) {
            $table->increments('id');
            
            $table->integer('formulir_id')->unsigned()->index('point_inventory_stock_opname_formulir_index');
            $table->foreign('formulir_id', 'point_inventory_stock_opname_formulir_foreign')
                ->references('id')->on('formulir')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->integer('warehouse_id')->unsigned()->index('point_inventory_stock_opname_warehouse_index');
            $table->foreign('warehouse_id', 'point_inventory_stock_opname_warehouse_foreign')
                ->references('id')->on('warehouse')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_inventory_stock_opname');
    }
}
