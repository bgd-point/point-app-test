<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnCashAdvancePointFinanceCashDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_finance_cash_cash_advance', function ($table) {

            $table->increments('id');

            $table->integer('point_finance_cash_id')->unsigned()->index('point_finance_cash_advance_cash_index');
            $table->foreign('point_finance_cash_id', 'point_finance_cash_advance_cash_foreign')
                ->references('id')->on('point_finance_cash')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('cash_advance_id')->unsigned()->nullable()->index('point_finance_cash_detail_cash_advance_index');
            $table->foreign('cash_advance_id', 'point_finance_cash_detail_cash_advance_foreign')
                ->references('id')->on('point_finance_cash_advance')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->boolean('close');

            $table->double('cash_advance_amount', 16, 4);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_finance_cash_cash_advance');
    }

}
