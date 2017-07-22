<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoaGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coa_group', function ($table) {
            $table->increments('id');

            $table->integer('coa_category_id')->unsigned()->index();
            $table->foreign('coa_category_id')
                ->references('id')->on('coa_category')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->string('name', 100);
            $table->string('coa_number')->unique()->nullable();
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
        Schema::drop('coa_group');
    }
}
