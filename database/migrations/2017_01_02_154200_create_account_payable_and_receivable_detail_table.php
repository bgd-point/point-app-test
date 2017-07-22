<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountPayableAndReceivableDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_payable_and_receivable_detail', function ($table) {
            $table->increments('id');

            $table->timestamp('form_date');

            $table->integer('account_payable_and_receivable_id')->unsigned()->index('account_payable_and_receivable_detail_parent_id_index');
            $table->foreign('account_payable_and_receivable_id', 'account_payable_and_receivable_detail_parent_id_foreign')
                ->references('id')->on('account_payable_and_receivable')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('formulir_reference_id')->unsigned()->index('account_payable_and_receivable_detail_reference_index');
            $table->foreign('formulir_reference_id', 'account_payable_and_receivable_detail_reference_foreign')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->decimal('amount', 16, 4);
            $table->text('notes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('account_payable_and_receivable_detail');
    }
}
