<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemUnitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_unit', function ($table) {
            $table->increments('id');

            $table->integer('item_id')->unsigned()->index();
            $table->foreign('item_id')
                ->references('id')->on('item')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->string('name');
            $table->decimal('converter', 15, 4)->unsigned()->default(1);
            $table->text('notes')->nullable();
            
            $table->nullableTimestamps();

            $table->boolean('as_default')->default(true);
            $table->boolean('disabled')->default(false);

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
        Schema::drop('item_unit');
    }
}
