<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointAccountingCutOffAccountDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_accounting_cut_off_account_detail', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('cut_off_account_id')->unsigned()->index('point_accounting_cut_off_account_id_index');
            $table->foreign('cut_off_account_id', 'point_accounting_cut_off_account_id_foreign')
                ->references('id')->on('point_accounting_cut_off_account')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('coa_id')->unsigned()->index();
            $table->foreign('coa_id')
                ->references('id')->on('coa')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->double('debit', 16, 4);
            $table->double('credit', 16, 4);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_accounting_cut_off_account_detail');
    }
}
