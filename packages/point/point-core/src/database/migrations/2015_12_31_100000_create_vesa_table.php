<?php

use Illuminate\Database\Migrations\Migration;

class CreateVesaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vesa', function ($table) {
            $table->increments('id');
            $table->timestamp('task_date')->useCurrent(); // task date given
            $table->timestamp('task_deadline')->useCurrent(); // task deadline
            $table->string('permission_slug'); // who responsible for this task
            $table->text('description'); // task description
            $table->string('task_action'); // action who close this task
            $table->integer('taskable_id')->index()->nullable(); // task from
            $table->string('taskable_type')->index()->nullable(); // task from
            $table->string('url'); // handle task link

            // Task done by
            $table->boolean('done')->default(false);
            $table->string('refer');
            $table->integer('user_id')->unsigned()->index()->nullable();
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onUpdate('restrict')
                ->onDelete('restrict');

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
        Schema::drop('vesa');
    }
}
