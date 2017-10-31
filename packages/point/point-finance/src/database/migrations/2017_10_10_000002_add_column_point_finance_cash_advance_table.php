<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnPointFinanceCashAdvanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('point_finance_cash_advance', function ($table) {
            $table->integer('coa_id')->unsigned()->default(1)->index('point_finance_cash_advance_coa_index');
            $table->foreign('coa_id', 'point_finance_cash_advance_coa_foreign')
                ->references('id')->on('coa')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('point_finance_cash_advance', function ($table) {
            $table->dropForeign('point_finance_cash_advance_coa_foreign');
            $table->dropColumn('coa_id');
        });
    }

}
