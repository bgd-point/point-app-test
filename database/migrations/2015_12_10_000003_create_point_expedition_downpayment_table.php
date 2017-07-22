<?php

use Illuminate\Database\Migrations\Migration;

class CreatePointExpeditionDownpaymentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_expedition_downpayment', function ($table) {
            $table->increments('id');

            $table->integer('formulir_id')->unsigned()->index('point_expedition_downpayment_formulir_index');
            $table->foreign('formulir_id', 'point_expedition_downpayment_formulir_foreign')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('expedition_id')->unsigned()->index('point_expedition_downpayment_expedition_index');
            $table->foreign('expedition_id', 'point_expedition_downpayment_expedition_foreign')
                ->references('id')->on('person')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->integer('expedition_order_id')->unsigned()->nullable();
            $table->decimal('amount', 16, 4);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_expedition_downpayment');
    }
}
