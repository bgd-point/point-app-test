<?php

use Illuminate\Database\Migrations\Migration;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function ($table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('value');
            $table->text('notes');
            $table->nullableTimestamps();

            $table->integer('created_by')->unsigned()->index()->default(1);
            $table->foreign('created_by')
                ->references('id')->on('users')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->integer('updated_by')->unsigned()->index()->default(1);
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
        Schema::drop('settings');
    }
}
