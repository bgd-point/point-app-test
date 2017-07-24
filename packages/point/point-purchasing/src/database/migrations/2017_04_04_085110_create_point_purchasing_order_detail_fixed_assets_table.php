<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointPurchasingOrderDetailFixedAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_purchasing_fixed_assets_order_detail', function ($table) {
            $table->increments('id');
            
            $table->integer('fixed_assets_order_id')->unsigned()->index('order_fa_detail_index');
            $table->foreign('fixed_assets_order_id', 'order_fa_detail_foreign')
                ->references('id')->on('point_purchasing_fixed_assets_order')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('coa_id')->unsigned()->index('order_fa_detail_coa_index');
            $table->foreign('coa_id', 'order_fa_detail_coa_foreign')
                ->references('id')->on('coa')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->integer('allocation_id')->unsigned()->index('order_fa_detail_allocation_index');
            $table->foreign('allocation_id', 'order_fa_detail_allocation_foreign')
                ->references('id')->on('allocation')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            
            $table->string('name');
            $table->decimal('quantity', 16, 4);
            $table->decimal('price', 16, 4);
            $table->decimal('discount', 16, 4);
            $table->string('unit');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_purchasing_fixed_assets_order_detail');
    }
}
