<?php

use Illuminate\Database\Migrations\Migration;

class CreatePointManufactureInputProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_manufacture_input_product', function ($table) {
            $table->increments('id');

            $table->integer('input_id')->unsigned()->index();
            $table->foreign('input_id')
                ->references('id')->on('point_manufacture_input')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('product_id')->unsigned()->index();
            $table->foreign('product_id')
                ->references('id')->on('item')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->integer('warehouse_id')->unsigned()->index();
            $table->foreign('warehouse_id')
                ->references('id')->on('warehouse')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->decimal('quantity', 16, 4);
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
        Schema::drop('point_manufacture_input_product');
    }
}
