<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReferTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('refer', function ($table) {
            $table->increments('id');

            $table->integer('by_id')->unsigned()->index()->nullable();
            $table->string('by_type')->index()->nullable();

            $table->integer('to_id')->unsigned()->index()->nullable();
            $table->string('to_type')->index()->nullable();

            $table->integer('to_parent_id')->unsigned()->index()->nullable();
            $table->string('to_parent_type')->index()->nullable();

            $table->decimal('value', 16, 4);
            $table->boolean('status')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('refer');
    }
}
