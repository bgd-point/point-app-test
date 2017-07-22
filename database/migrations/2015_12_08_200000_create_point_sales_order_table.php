<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointSalesOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_sales_order', function ($table) {
            $table->increments('id');
            
            $table->integer('formulir_id')->unsigned()->index('point_sales_order_formulir_index');
            $table->foreign('formulir_id', 'point_sales_order_formulir_foreign')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('person_id')->unsigned()->index('point_sales_order_person_index');
            $table->foreign('person_id', 'point_sales_order_person_foreign')
                ->references('id')->on('person')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            // if cash, then user need to make a downpayment before
            // user can create a delivery order
            $table->boolean('is_cash');

            // if not include expedition, so user need to make an order
            // from other vendor (expedition feature)
            $table->boolean('include_expedition')->default(false);
            $table->decimal('expedition_fee', 16, 4); // for include expedition only, otherwise it should be 0

            $table->string('type_of_tax');
            $table->decimal('subtotal', 16, 4);
            $table->decimal('discount', 16, 4);
            $table->decimal('tax_base', 16, 4);
            $table->decimal('tax', 16, 4);
            $table->decimal('total', 16, 4);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_sales_order');
    }
}
