<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFormulirLockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('formulir_lock', function ($table) {
            $table->increments('id');
            // base on formulir id
            $table->integer('locking_id')->unsigned()->index();
            $table->foreign('locking_id', 'locking_id_formulir')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->integer('locked_id')->unsigned()->index();
            $table->foreign('locked_id', 'locked_id_formulir')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->boolean('locked')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('formulir_lock');
    }
}
