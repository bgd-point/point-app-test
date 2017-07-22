<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointFinancePaymentOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_finance_payment_order', function ($table) {
            $table->increments('id');
            
            $table->integer('formulir_id')->unsigned()->index();
            $table->foreign('formulir_id')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('person_id')->unsigned()->index();
            $table->foreign('person_id')
                ->references('id')->on('person')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->string('payment_type'); // cash or bank
            $table->integer('payment_type_coa_id')->nullable()->unsigned()->index('point_finance_payment_order_coa_index');
            $table->foreign('payment_type_coa_id', 'point_finance_payment_order_coa_foreign')
                ->references('id')->on('coa')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->double('total', 16, 4);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_finance_payment_order');
    }
}
