<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointSalesDeliveryOrderItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_sales_delivery_order_item', function ($table) {
            $table->increments('id');
            
            $table->integer('point_sales_delivery_order_id')->unsigned()->index('point_sales_delivery_order_bpro_index');
            $table->foreign('point_sales_delivery_order_id', 'point_sales_delivery_order_bpro_foreign')
                ->references('id')->on('point_sales_delivery_order')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('item_id')->unsigned()->index('point_sales_delivery_order_item_index');
            $table->foreign('item_id', 'point_sales_delivery_order_item_foreign')
                ->references('id')->on('item')
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
        Schema::drop('point_sales_delivery_order_item');
    }
}
