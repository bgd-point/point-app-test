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
        Schema::table('point_finance_cash_detail', function ($table) {
            $table->integer('cash_advance_id')->unsigned()->nullable()->index('point_finance_cash_detail_cash_advance_index');
            $table->foreign('cash_advance_id', 'point_finance_cash_detail_cash_advance_foreign')
                ->references('id')->on('point_finance_cash_advance')
                ->onUpdate('restrict')
                ->onDelete('restrict');
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
        Schema::table('point_finance_cash_detail', function ($table) {
            $table->dropForeign('point_finance_cash_detail_cash_advance_foreign');
            $table->dropColumn('cash_advance_id');
            $table->dropColumn('cash_advance_amount');
        });
    }

}
