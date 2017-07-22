<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoaGroupCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coa_group_category', function ($table) {
            $table->increments('id');

            $table->integer('coa_position_id')->unsigned()->index();
            $table->foreign('coa_position_id')
                ->references('id')->on('coa_position')
                ->onUpdate('restrict')
                ->onDelete('restrict');
                
            $table->string('name', 100);
            $table->string('coa_number')->unique()->nullable();
            $table->text('notes')->nullable();
            $table->nullableTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('coa_group_category');
    }
}
