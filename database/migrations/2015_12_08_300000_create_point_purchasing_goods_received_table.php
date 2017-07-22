<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointPurchasingGoodsReceivedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_purchasing_goods_received', function ($table) {
            $table->increments('id');
            
            $table->integer('formulir_id')->unsigned()->index('point_purchasing_goods_received_formulir_index');
            $table->foreign('formulir_id', 'point_purchasing_goods_received_formulir_foreign')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            
            $table->integer('warehouse_id')->unsigned()->index('point_purchasing_goods_received_warehouse_index');
            $table->foreign('warehouse_id', 'point_purchasing_goods_received_warehouse_foreign')
                ->references('id')->on('warehouse')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            
            $table->integer('point_purchasing_order_id')->unsigned()->index('purchasing_order_index');
            $table->foreign('point_purchasing_order_id', 'purchasing_order_item_foreign')
                ->references('id')->on('point_purchasing_order')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('supplier_id')->unsigned()->index('point_purchasing_receive_supplier_index');
            $table->foreign('supplier_id', 'point_purchasing_receive_supplier_foreign')
                ->references('id')->on('person')
                ->onUpdate('restrict')
                ->onDelete('restrict');

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
        Schema::drop('point_purchasing_goods_received');
    }
}
