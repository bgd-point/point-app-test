<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointAccountingMemoJournalDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_accounting_memo_journal_detail', function ($table) {
            $table->increments('id');
            
            $table->integer('memo_journal_id')->unsigned()->index();
            $table->foreign('memo_journal_id')
                ->references('id')->on('point_accounting_memo_journal')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('coa_id')->unsigned()->index();
            $table->foreign('coa_id')
                ->references('id')->on('coa')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->double('debit', 16, 4);
            $table->double('credit', 16, 4);

            $table->integer('form_journal_id')->unsigned()->index();
            $table->foreign('form_journal_id')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('form_reference_id')->nullable()->unsigned()->index();
            $table->foreign('form_reference_id')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('subledger_id');
            $table->string('subledger_type', 255);

            $table->text('description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_accounting_memo_journal_detail');
    }
}
