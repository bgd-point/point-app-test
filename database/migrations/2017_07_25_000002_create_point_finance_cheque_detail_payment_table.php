<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointFinanceChequeDetailPaymentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_finance_cheque_detail_payment', function ($table) {
            $table->increments('id');
            
            $table->integer('point_finance_cheque_id')->unsigned()->index('point_finance_cheque_detail_payment_index');
            $table->foreign('point_finance_cheque_id', 'point_finance_cheque_detail_payment_foreign')
                ->references('id')->on('point_finance_cheque')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('coa_id')->unsigned()->index();
            $table->foreign('coa_id')
                ->references('id')->on('coa')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->integer('allocation_id')->unsigned()->index();
            $table->foreign('allocation_id')
                ->references('id')->on('allocation')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->text('notes_detail');
            $table->decimal('amount', 16, 4);
            $table->integer('reference_id')->index()->nullable();
            $table->string('reference_type')->index()->nullable();
            $table->integer('form_reference_id')->unsigned()->nullable();
            $table->integer('subledger_id')->unsigned()->nullable();
            $table->string('subledger_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_finance_cheque_detail_payment');
    }
}
