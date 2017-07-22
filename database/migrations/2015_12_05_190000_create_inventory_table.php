<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInventoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory', function ($table) {
            $table->increments('id');

            $table->integer('formulir_id')->unsigned()->index();
            $table->foreign('formulir_id')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->timestamp('form_date')->useCurrent();

            $table->integer('warehouse_id')->unsigned()->index();
            $table->foreign('warehouse_id')
                ->references('id')->on('warehouse')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->integer('item_id')->unsigned()->index();
            $table->foreign('item_id')
                ->references('id')->on('item')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->decimal('quantity', 16, 4);
            $table->decimal('price', 16, 4)->default(0);
            
            /**
             * Cogs or Cost of goods sale, total_quantity, and total_value
             * will calculated from previous rows
             */
            $table->decimal('cogs', 16, 4)->default(0);
            $table->decimal('total_quantity', 16, 4)->default(0);
            $table->decimal('total_value', 16, 4)->default(0);

            /**
             * If true it's indicate that this type of inventory
             * need to be recalculate
             */
            $table->boolean('recalculate')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('inventory');
    }
}
