<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableEmailHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_history', function ($table) {
            $table->increments('id');
            $table->timestamp('sent_at');
            $table->integer('sender')->unsigned();
            $table->integer('recipient')->unsigned();
            $table->string('recipient_email', 255);
            $table->integer('formulir_id')->unsigned();
            
            $table->foreign('sender')
                ->references('id')->on('users')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->foreign('recipient')
                ->references('id')->on('person')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->foreign('formulir_id')
                ->references('id')->on('formulir')
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
        Schema::drop('email_history');
    }
}
