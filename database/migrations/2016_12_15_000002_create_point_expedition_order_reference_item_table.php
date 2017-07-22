<?php

use Illuminate\Database\Migrations\Migration;

class CreatePointExpeditionOrderReferenceItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_expedition_order_reference_item', function ($table) {
            $table->increments('id');

            $table->integer('point_expedition_order_reference_id')->unsigned()->index('point_expedition_order_reference_item_exo_index');
            $table->foreign('point_expedition_order_reference_id', 'point_expedition_order_reference_item_exo_foreign')
                ->references('id')->on('point_expedition_order_reference')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('item_id')->unsigned()->index('point_expedition_order_reference_item_item_index');
            $table->foreign('item_id', 'point_expedition_order_reference_item_item_foreign')
                ->references('id')->on('item')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->integer('allocation_id')->unsigned()->index('point_expedition_order_reference_item_allocation_index');
            $table->foreign('allocation_id', 'point_expedition_order_reference_item_allocation_foreign')
                ->references('id')->on('allocation')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->decimal('quantity', 16, 4);
            $table->decimal('price', 16, 4);
            $table->decimal('discount', 16, 4);
            $table->string('unit');
            $table->decimal('converter', 16, 4);
            $table->string('item_notes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_expedition_order_reference_item');
    }
}
