<?php

use Illuminate\Database\Migrations\Migration;

class CreatePointManufactureOutputTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_manufacture_output', function ($table) {
            $table->increments('id');

            $table->integer('formulir_id')->unsigned()->index();
            $table->foreign('formulir_id')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('input_id')->unsigned()->index();
            $table->foreign('input_id')
                ->references('id')->on('point_manufacture_input')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('machine_id')->unsigned()->index();
            $table->foreign('machine_id')
                ->references('id')->on('point_manufacture_machine')
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
        Schema::drop('point_manufacture_output');
    }
}
