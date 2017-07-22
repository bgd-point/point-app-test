<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointFinanceBankTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_finance_bank', function ($table) {
            $table->increments('id');
            
            $table->integer('formulir_id')->unsigned()->index();
            $table->foreign('formulir_id')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('coa_id')->unsigned()->index();
            $table->foreign('coa_id')
                ->references('id')->on('coa')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('person_id')->unsigned()->index('point_finance_bank_person_index');
            $table->foreign('person_id', 'point_finance_bank_person_foreign')
                ->references('id')->on('person')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->string('payment_flow'); // in or out
            $table->decimal('total', 16, 4);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_finance_bank');
    }
}
