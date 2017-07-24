<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePersonTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('person', function ($table) {
            $table->increments('id');

            $table->integer('person_type_id')->unsigned()->index();
            $table->foreign('person_type_id')
                ->references('id')->on('person_type')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->integer('person_group_id')->unsigned()->index();
            $table->foreign('person_group_id')
                ->references('id')->on('person_group')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->string('code')->unique();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->text('notes')->nullable();

            $table->nullableTimestamps();

            $table->boolean('disabled')->default(false);

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
        Schema::drop('person');
    }
}
