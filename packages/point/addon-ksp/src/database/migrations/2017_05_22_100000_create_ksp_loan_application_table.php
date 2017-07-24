<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKspLoanApplicationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ksp_loan_application', function ($table) {
            $table->increments('id');

            $table->timestamp('date_of_realization')->nullable();

            $table->integer('customer_id')->unsigned()->index();
            $table->foreign('customer_id')
                ->references('id')->on('person')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->integer('formulir_id')->unsigned()->index();
            $table->foreign('formulir_id')
                ->references('id')->on('formulir')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->decimal('loan_amount', 16, 4);
            $table->integer('periods');
            $table->decimal('interest_rate', 16, 4);
            $table->string('interest_rate_type');
            $table->string('payment_type');
            $table->integer('payment_account_id')->unsigned()->index();
            $table->foreign('payment_account_id')
                ->references('id')->on('coa')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->nullableTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ksp_loan_application');
    }
}
