<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointInventoryTransferItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_inventory_transfer_item', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('formulir_id')->unsigned()->index('point_inventory_transfer_item_formulir_id_index');
            $table->foreign('formulir_id', 'point_inventory_transfer_item_formulir_id_foreign')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');
                
            $table->integer('warehouse_sender_id')->unsigned()->index();
            $table->foreign('warehouse_sender_id')
                  ->references('id')->on('warehouse')
                  ->onUpdate('restrict')
                  ->onDelete('restrict');
                  
            $table->integer('warehouse_receiver_id')->unsigned()->index();
            $table->foreign('warehouse_receiver_id')
                  ->references('id')->on('warehouse')
                  ->onUpdate('restrict')
                  ->onDelete('restrict');
                  
            $table->timestamp('received_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_inventory_transfer_item');
    }
}
