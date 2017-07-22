<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOpeningInventoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('opening_inventory', function ($table) {
            $table->increments('id');
            
            $table->integer('formulir_id')->unsigned()->index();
            $table->foreign('formulir_id')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('item_id')->unsigned()->index()->nullable();
            $table->foreign('item_id')
                ->references('id')->on('item')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->decimal('quantity', 16, 4);
            $table->decimal('price', 16, 4);
            $table->string('unit');
            $table->decimal('converter', 16, 4);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('opening_inventory');
    }
}
