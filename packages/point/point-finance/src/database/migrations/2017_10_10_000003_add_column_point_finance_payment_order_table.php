<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnPointFinancePaymentOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('point_finance_payment_order', function ($table) {
            $table->integer('cash_advance_id')->unsigned()->nullable()->index('point_finance_payment_order_cash_advance_index');
            $table->foreign('cash_advance_id', 'point_finance_payment_order_cash_advance_foreign')
                ->references('id')->on('point_finance_cash_advance')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });

        Schema::table('point_finance_payment_reference', function ($table) {
            $table->integer('cash_advance_id')->unsigned()->nullable()->index('point_finance_payment_reference_cash_advance_index');
            $table->foreign('cash_advance_id', 'point_finance_payment_reference_cash_advance_foreign')
                ->references('id')->on('point_finance_cash_advance')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });

        Schema::table('point_finance_cash', function ($table) {
            $table->integer('cash_advance_id')->unsigned()->nullable()->index('point_finance_cash_cash_advance_index');
            $table->foreign('cash_advance_id', 'point_finance_cash_cash_advance_foreign')
                ->references('id')->on('point_finance_cash_advance')
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
        Schema::table('point_finance_payment_order', function ($table) {
            $table->dropForeign('point_finance_payment_order_cash_advance_foreign');
            $table->dropColumn('cash_advance_id');
        });
        Schema::table('point_finance_payment_reference', function ($table) {
            $table->dropForeign('point_finance_payment_reference_cash_advance_foreign');
            $table->dropColumn('cash_advance_id');
        });
        Schema::table('point_finance_cash', function ($table) {
            $table->dropForeign('point_finance_cash_cash_advance_foreign');
            $table->dropColumn('cash_advance_id');
        });
    }

}
