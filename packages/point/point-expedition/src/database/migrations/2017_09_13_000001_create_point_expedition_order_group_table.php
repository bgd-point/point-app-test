<?php

use Illuminate\Database\Migrations\Migration;

class CreatePointExpeditionOrderGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_expedition_order_group', function ($table) {
            $table->increments('id');
            $table->integer('formulir_id')->unsigned()->index('point_expedition_order_group_formulir_index');
            $table->foreign('formulir_id', 'point_expedition_order_group_formulir_foreign')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            });
    }

    public function down()
    {
        Schema::drop('point_expedition_order_group');
    }
}