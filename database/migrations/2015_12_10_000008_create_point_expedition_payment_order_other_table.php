<?php

use Illuminate\Database\Migrations\Migration;

class CreatePointExpeditionPaymentOrderOtherTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_expedition_payment_order_other', function ($table) {
            $table->increments('id');

            $table->integer('point_expedition_payment_order_id')->unsigned()->index('point_expedition_payment_order_other_bppo_index');
            $table->foreign('point_expedition_payment_order_id', 'point_expedition_payment_order_other_bppo_foreign')
                ->references('id')->on('point_expedition_payment_order')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('coa_id')->unsigned()->index('point_expedition_payment_order_other_coa_index');
            $table->foreign('coa_id', 'point_expedition_payment_order_other_coa_foreign')
                ->references('id')->on('coa')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->integer('allocation_id')->unsigned()->index('point_expedition_payment_order_other_allocation_index');
            $table->foreign('allocation_id', 'point_expedition_payment_order_other_allocation_foreign')
                ->references('id')->on('allocation')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->text('other_notes');
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
        Schema::drop('point_expedition_payment_order_other');
    }
}
