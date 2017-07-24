<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointInventoryUsage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_inventory_usage', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('formulir_id')->unsigned()->index();
            $table->foreign('formulir_id')
                  ->references('id')->on('formulir')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');

            $table->integer('warehouse_id')->unsigned()->index();
            $table->foreign('warehouse_id')
                  ->references('id')->on('warehouse')
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
        Schema::drop('point_inventory_usage');
    }
}
