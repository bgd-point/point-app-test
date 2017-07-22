<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointInventoryTransferItemDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_inventory_transfer_item_detail', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('transfer_item_id')->unsigned()->index();
            $table->foreign('transfer_item_id')
                  ->references('id')->on('point_inventory_transfer_item')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->integer('item_id')->unsigned()->index('point_inventory_transfer_item_detail_index');
            $table->foreign('item_id', 'point_inventory_transfer_item_detail_foreign')
                ->references('id')->on('item')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->decimal('qty_send', 16, 4);
            $table->decimal('qty_received', 16, 4);
            $table->string('unit', 50);
            $table->decimal('converter', 16, 4);
            $table->decimal('cogs', 16, 4);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_inventory_transfer_item_detail');
    }
}
