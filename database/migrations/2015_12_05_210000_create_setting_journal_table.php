<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingJournalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('setting_journal', function ($table) {
            $table->increments('id');

            $table->string('group');
            $table->string('name');
            $table->text('description');

            $table->integer('coa_id')->unsigned()->index()->nullable();
            $table->foreign('coa_id')
                ->references('id')->on('coa')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            ;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('setting_journal');
    }
}
