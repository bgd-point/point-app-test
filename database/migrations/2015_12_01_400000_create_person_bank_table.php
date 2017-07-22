<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePersonBankTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('person_bank', function ($table) {
            $table->increments('id');

            $table->integer('person_id')->unsigned()->index();
            $table->foreign('person_id')
                ->references('id')->on('person')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->string('branch')->nullable();
            $table->string('name');
            $table->string('account_number');
            $table->string('account_name');
            $table->text('notes')->nullable();

            $table->nullableTimestamps();

            $table->integer('created_by')->unsigned()->index();
            $table->foreign('created_by')
                ->references('id')->on('users')
                ->onUpdate('restrict')
                ->onDelete('restrict');
                
            $table->integer('updated_by')->unsigned()->index();
            $table->foreign('updated_by')
                ->references('id')->on('users')
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
        Schema::drop('person_bank');
    }
}
