<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointSalesPosPricingItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_sales_pos_pricing_item', function ($table) {
            $table->increments('id');
            
            $table->integer('pos_pricing_id')->unsigned()->index('point_sales_pos_pricing_item_pos_pricing_index');
            $table->foreign('pos_pricing_id', 'point_sales_pos_pricing_item_pos_pricing_foreign')
                ->references('id')->on('point_sales_pos_pricing')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('person_group_id')->unsigned()->index('point_sales_pos_pricing_item_group_index');
            $table->foreign('person_group_id', 'point_sales_pos_pricing_item_group_foreign')
                ->references('id')->on('person_group')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('item_id')->unsigned()->index('point_sales_pos_pricing_item_item_index');
            $table->foreign('item_id', 'point_sales_pos_pricing_item_item_foreign')
                ->references('id')->on('item')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->double('price', 16, 4);
            $table->double('discount', 16, 4);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_sales_pos_pricing_item');
    }
}
