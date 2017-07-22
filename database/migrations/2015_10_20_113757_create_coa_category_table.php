<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoaCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coa_category', function ($table) {
            $table->increments('id');

            $table->integer('coa_position_id')->unsigned()->index();
            $table->foreign('coa_position_id')
                ->references('id')->on('coa_position')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->integer('coa_group_category_id')->unsigned()->index()->nullable();
            $table->foreign('coa_group_category_id', 'fk_cg_id')
                ->references('id')->on('coa_group_category')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->string('name', 100);
            $table->string('coa_number')->unique()->nullable();
            $table->boolean('has_subledger')->default(false);
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
        Schema::drop('coa_category');
    }
}
