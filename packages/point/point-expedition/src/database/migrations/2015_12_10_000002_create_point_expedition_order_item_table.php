<?php

use Illuminate\Database\Migrations\Migration;

class CreatePointExpeditionOrderItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_expedition_order_item', function ($table) {
            $table->increments('id');

            $table->integer('point_expedition_order_id')->unsigned()->index('point_expedition_order_item_bpo_index');
            $table->foreign('point_expedition_order_id', 'point_expedition_order_item_bpo_foreign')
                ->references('id')->on('point_expedition_order')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('item_id')->unsigned()->index('point_expedition_order_item_item_index');
            $table->foreign('item_id', 'point_expedition_order_item_item_foreign')
                ->references('id')->on('item')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->decimal('quantity', 16, 4);
            $table->decimal('price', 16, 4);
            $table->string('item_notes');
            $table->decimal('item_fee', 16, 4);
            $table->string('unit');
            $table->decimal('converter', 16, 4);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_expedition_order_item');
    }
}
