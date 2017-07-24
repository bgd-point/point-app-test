<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointInventoryStockCorrectionItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_inventory_stock_correction_item', function ($table) {
            $table->increments('id');

            $table->integer('point_inventory_stock_correction_id')->unsigned()->index('point_inventory_stock_correction_parent_index');
            $table->foreign('point_inventory_stock_correction_id', 'point_inventory_stock_correction_parent_foreign')
                ->references('id')->on('point_inventory_stock_correction')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            
            $table->integer('item_id')->unsigned()->index();
            $table->foreign('item_id')
                ->references('id')->on('item')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->integer('stock_in_database'); // stock in database when stock correction happend
            $table->integer('quantity_correction'); // how much correction in stock, ex: 1 or -1, 0 is not allowed
            $table->string('unit');
            $table->decimal('converter', 16, 4);
            $table->text('correction_notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_inventory_stock_correction_item');
    }
}
