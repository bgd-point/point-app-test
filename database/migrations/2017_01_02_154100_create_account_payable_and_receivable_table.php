<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountPayableAndReceivableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_payable_and_receivable', function ($table) {
            $table->increments('id');

            $table->timestamp('form_date');

            $table->integer('formulir_reference_id')->unsigned()->index();
            $table->foreign('formulir_reference_id')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('account_id')->unsigned()->index();
            $table->foreign('account_id')
                ->references('id')->on('coa')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->decimal('amount', 16, 4);

            $table->string('type', 10); // payable or receivable
            $table->text('notes');

            $table->integer('person_id')->unsigned()->index();
            $table->foreign('person_id')
                ->references('id')->on('person')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->boolean('done')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('account_payable_and_receivable');
    }
}
