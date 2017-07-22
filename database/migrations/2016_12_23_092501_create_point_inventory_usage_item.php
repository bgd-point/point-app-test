<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointInventoryUsageItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_inventory_usage_item', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('inventory_usage_id')->unsigned()->index();
            $table->foreign('inventory_usage_id')
                  ->references('id')->on('point_inventory_usage')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            
            $table->integer('item_id')->unsigned()->index();
            $table->foreign('item_id')
                  ->references('id')->on('item')
                  ->onUpdate('restrict')
                  ->onDelete('restrict');
                
            $table->string('unit');
            $table->integer('converter');

            $table->decimal('stock_in_database', 16, 4);
            $table->decimal('quantity_usage', 16, 4);
            $table->string('usage_notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_inventory_usage_item');
    }
}
