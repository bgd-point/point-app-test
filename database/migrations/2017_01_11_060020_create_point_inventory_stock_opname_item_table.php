<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointInventoryStockOpnameItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_inventory_stock_opname_item', function ($table) {
            $table->increments('id');
            
            $table->integer('stock_opname_id')->unsigned()->index('point_inventory_stock_opname_item_index');
            $table->foreign('stock_opname_id', 'point_inventory_stock_opname_item_foreign')
            ->references('id')->on('point_inventory_stock_opname')
            ->onUpdate('cascade')
            ->onDelete('cascade');
            
            $table->integer('item_id')->unsigned()->index();
            $table->foreign('item_id')
            ->references('id')->on('item')
            ->onUpdate('restrict')
            ->onDelete('restrict');
            
            $table->integer('stock_in_database');
            $table->integer('quantity_opname');
            $table->string('unit');
            $table->integer('converter')->nullable();
            $table->string('opname_notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_inventory_stock_opname_item');
    }
}
