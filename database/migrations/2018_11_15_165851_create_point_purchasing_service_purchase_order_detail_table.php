<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointPurchasingServicePurchaseOrderDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     * 
     */
    public function up()
    {
        Schema::create('point_purchasing_service_purchase_order_detail', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('purchase_order_id')->unsigned()->index('service_purchase_order_detail_purchase_order_id_index');
            $table->foreign('purchase_order_id', 'service_purchase_order_detail_purchase_order_id_foreign')
                ->references('id')->on('point_purchasing_service_purchase_order')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('item_id')->unsigned()->index('service_purchase_order_detail_item_id_index');
            $table->foreign('item_id', 'service_purchase_order_detail_item_id_foreign')
                ->references('id')->on('item')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->integer('allocation_id')->unsigned()->index('service_purchase_order_detail_allocation_id_index');
            $table->foreign('allocation_id', 'service_purchase_order_detail_allocation_id_foreign')
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
        Schema::drop('point_purchasing_service_purchase_order_detail');
    }
}
