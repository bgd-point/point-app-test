<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointFinanceChequeDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_finance_cheque_detail', function ($table) {
            $table->increments('id');
            
            $table->integer('point_finance_cheque_id')->unsigned()->index('point_finance_cheque_detail_index');
            $table->foreign('point_finance_cheque_id', 'point_finance_cheque_detail_foreign')
                ->references('id')->on('point_finance_cheque')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->timestamp('due_date')->useCurrent();
            $table->timestamp('disbursement_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->integer('disbursement_coa_id')->unsigned()->nullable()->index('disbursement_coa_id_index');
            $table->foreign('disbursement_coa_id', 'disbursement_coa_id_foreign')
                ->references('id')->on('coa')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            
            $table->string('number')->index()->nullable();
            $table->string('bank')->index()->nullable();
            $table->integer('rejected_counter')->unsigned();
            $table->decimal('amount', 16, 4);
            $table->text('notes');
            $table->boolean('status')->default(false); // 1 = disbursement, -1 = rejected, 0 = pending, 2 = close permanent
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_finance_cheque_detail');
    }
}
