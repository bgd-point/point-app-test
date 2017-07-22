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
            $table->integer('locking_id')->index();
            $table->integer('locked_id')->index();
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
