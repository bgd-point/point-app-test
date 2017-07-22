<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointAccountingCutOffReceivableDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_accounting_cut_off_receivable_detail', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('cut_off_receivable_id')->unsigned()->index('point_accounting_cut_off_receivable_id_index');
            $table->foreign('cut_off_receivable_id', 'point_accounting_cut_off_receivable_id_foreign')
                ->references('id')->on('point_accounting_cut_off_receivable')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('coa_id')->unsigned()->index();
            $table->foreign('coa_id')
                ->references('id')->on('coa')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->integer('subledger_id');
            $table->string('subledger_type', 255);
            $table->double('amount', 16, 4);
            $table->string('notes', 255);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_accounting_cut_off_receivable_detail');
    }
}
