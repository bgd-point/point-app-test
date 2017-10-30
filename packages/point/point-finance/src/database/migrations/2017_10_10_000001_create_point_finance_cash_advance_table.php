<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointFinanceCashAdvanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_finance_cash_advance', function ($table) {
            $table->increments('id');

            $table->integer('formulir_id')->unsigned()->index('point_finance_cash_advance_formulir_index');
            $table->foreign('formulir_id', 'point_finance_cash_advance_formulir_foreign')
                ->references('id')->on('formulir')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->integer('employee_id')->unsigned()->index('point_finance_cash_advance_employee_index');
            $table->foreign('employee_id', 'point_finance_cash_advance_employee_foreign')
                ->references('id')->on('person')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->string('payment_type')->nullable()->default('cash');

            $table->decimal('amount', 16, 4);
            $table->decimal('remaining_amount', 16, 4);
            $table->boolean('is_payed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_finance_cash_advance');
    }

}
