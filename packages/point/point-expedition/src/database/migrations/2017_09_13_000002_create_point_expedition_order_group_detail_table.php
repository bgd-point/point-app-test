<?php

use Illuminate\Database\Migrations\Migration;

class CreatePointExpeditionOrderGroupDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_expedition_order_group_detail', function ($table) {
            $table->increments('id');
            $table->integer('point_expedition_order_group_id')->unsigned()->index('peo_group_detail_formulir_index');
            $table->foreign('point_expedition_order_group_id', 'peo_group_detail_formulir_foreign')
                ->references('id')->on('point_expedition_order_group')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('point_expedition_order_id')->unsigned()->index('peo_detail_formulir_index');
            $table->foreign('point_expedition_order_id', 'peo_detail_formulir_foreign')
                ->references('id')->on('point_expedition_order')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            });
    }

    public function down()
    {
        Schema::drop('point_expedition_order_group_detail');
    }
}