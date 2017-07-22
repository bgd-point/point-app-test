<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coa', function ($table) {
            $table->increments('id');

            $table->integer('coa_category_id')->unsigned()->index();
            $table->foreign('coa_category_id')
                ->references('id')->on('coa_category')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->integer('coa_group_id')->unsigned()->index()->nullable();
            $table->foreign('coa_group_id')
                ->references('id')->on('coa_group')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->string('name', 100)->unique();
            $table->string('coa_number')->unique()->nullable();
            $table->text('notes')->nullable();

            /**
             * Coa is cannot be deleted after used, so you can hide by disabled this
             */
            $table->boolean('disabled')->default(false);

            $table->boolean('has_subledger')->default(false);

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
        Schema::drop('coa');
    }
}
