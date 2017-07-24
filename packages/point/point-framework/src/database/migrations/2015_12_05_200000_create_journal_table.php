<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJournalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('journal', function ($table) {
            $table->increments('id');

            $table->timestamp('form_date')->useCurrent();

            $table->integer('coa_id')->unsigned()->index();
            $table->foreign('coa_id')
                ->references('id')->on('coa')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->text('description');

            $table->decimal('debit', 16, 4);
            $table->decimal('credit', 16, 4);

            /**
             * When some formulir created and write in journal, this column will
             * reference them as well. so we know where is it come from
             */

            $table->integer('form_journal_id')->unsigned()->index();
            $table->foreign('form_journal_id')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            ;

            /**
             * When some of formulir references another formulir, we will save that referencese
             * in this column, so you can compare which formulir still not completed
             */
            $table->integer('form_reference_id')->unsigned()->index()->nullable();
            $table->foreign('form_reference_id')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            /**
             * This subledger is collection of data from multiple table, so you
             * can call this subledger account to get all expected journal
             */
            $table->integer('subledger_id')->unsigned()->index()->nullable();
            $table->string('subledger_type')->index()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('journal');
    }
}
