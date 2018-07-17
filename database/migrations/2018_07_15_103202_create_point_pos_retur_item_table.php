<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointPosReturItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_sales_pos_retur_item', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('pos_retur_id')->unsigned()->index('point_sales_pos_retur_item_pos_retur_index');
            $table->foreign('pos_retur_id', 'point_sales_pos_retur_item_pos_retur_foreign')
                ->references('id')->on('point_sales_pos_retur')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('warehouse_id')->unsigned()->index('point_sales_pos_retur_item_warehouse_index');
            $table->foreign('warehouse_id', 'point_sales_pos_retur_item_warehouse_foreign')
                ->references('id')->on('warehouse')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->integer('item_id')->unsigned()->index('point_sales_pos_retur_item_item_index');
            $table->foreign('item_id', 'point_sales_pos_retur_item_item_foreign')
                ->references('id')->on('item')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->decimal('quantity', 16, 4);
            $table->decimal('quantity_retur', 16, 4);
            $table->decimal('total', 16, 4);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_sales_pos_retur_item');
    }
}
