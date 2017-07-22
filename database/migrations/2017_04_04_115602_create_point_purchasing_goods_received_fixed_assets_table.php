<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointPurchasingGoodsReceivedFixedAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_purchasing_fixed_assets_goods_received', function ($table) {
            $table->increments('id');
            
            $table->integer('formulir_id')->unsigned()->index('fa_goods_received_formulir_index');
            $table->foreign('formulir_id', 'fa_goods_received_formulir_foreign')
                ->references('id')->on('formulir')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            
            $table->integer('warehouse_id')->unsigned()->index('fa_goods_received_warehouse_index');
            $table->foreign('warehouse_id', 'fa_goods_received_warehouse_foreign')
                ->references('id')->on('warehouse')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            
            $table->integer('fixed_assets_order_id')->unsigned()->index('fa_order_good_received_index');
            $table->foreign('fixed_assets_order_id', 'fa_order_good_received_item_foreign')
                ->references('id')->on('point_purchasing_fixed_assets_order')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('supplier_id')->unsigned()->index('fa_receive_supplier_index');
            $table->foreign('supplier_id', 'fa_receive_supplier_foreign')
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
        Schema::drop('point_purchasing_fixed_assets_goods_received');
    }
}
