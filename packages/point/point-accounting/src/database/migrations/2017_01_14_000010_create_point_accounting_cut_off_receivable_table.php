<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointAccountingCutOffReceivableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_accounting_cut_off_receivable', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('formulir_id')->unsigned()->index();
            $table->foreign('formulir_id')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_accounting_cut_off_receivable');
    }
}
