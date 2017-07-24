<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointSalesDeliveryOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_sales_delivery_order', function ($table) {
            $table->increments('id');
            
            $table->integer('formulir_id')->unsigned()->index('point_sales_delivery_order_formulir_index');
            $table->foreign('formulir_id', 'point_sales_delivery_order_formulir_foreign')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            
            $table->integer('warehouse_id')->unsigned()->index('point_sales_delivery_order_warehouse_index');
            $table->foreign('warehouse_id', 'point_sales_delivery_order_warehouse_foreign')
                ->references('id')->on('warehouse')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->integer('person_id')->unsigned()->index('point_sales_delivery_person_index');
            $table->foreign('person_id', 'point_sales_delivery_person_foreign')
                ->references('id')->on('person')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->integer('point_sales_order_id')->unsigned()->index('sales_order_index');
            $table->foreign('point_sales_order_id', 'sales_order_item_foreign')
                ->references('id')->on('point_sales_order')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->string('driver');
            $table->string('license_plate');

            $table->boolean('include_expedition')->default(false);
            $table->decimal('expedition_fee', 16, 4);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_sales_delivery_order');
    }
}
