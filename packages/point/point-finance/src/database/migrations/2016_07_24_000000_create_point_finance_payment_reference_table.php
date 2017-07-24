<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointFinancePaymentReferenceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_finance_payment_reference', function ($table) {
            $table->increments('id');
            
            $table->integer('point_finance_payment_id')->nullable()->unsigned()->index('point_finance_payment_reference_formulir_index');
            $table->foreign('point_finance_payment_id', 'point_finance_payment_reference_formulir_foreign')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('payment_reference_id')->unsigned()->index('point_finance_payment_reference_formulir_reference_index');
            $table->foreign('payment_reference_id', 'point_finance_payment_reference_formulir_reference_foreign')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('person_id')->unsigned()->index('point_finance_payment_reference_person_index');
            $table->foreign('person_id', 'point_finance_payment_reference_person_foreign')
                ->references('id')->on('person')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->string('payment_flow'); // in or out
            $table->string('payment_type'); // cash or bank
            $table->integer('payment_type_coa_id')->nullable()->unsigned()->index('point_finance_payment_reference_coa_index');
            $table->foreign('payment_type_coa_id', 'point_finance_payment_reference_coa_foreign')
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
        Schema::drop('point_finance_payment_reference');
    }
}
