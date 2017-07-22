<?php

use Illuminate\Database\Migrations\Migration;

class CreatePointManufactureFormulaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_manufacture_formula', function ($table) {
            $table->increments('id');
            $table->string('name');

            $table->integer('formulir_id')->unsigned()->index();
            $table->foreign('formulir_id')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('process_id')->unsigned()->index();
            $table->foreign('process_id')
                ->references('id')->on('point_manufacture_process')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_manufacture_formula');
    }
}
