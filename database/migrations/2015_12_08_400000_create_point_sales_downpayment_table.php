<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointSalesDownpaymentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_sales_downpayment', function ($table) {
            $table->increments('id');
            
            $table->integer('formulir_id')->unsigned()->index('point_sales_downpayment_formulir_index');
            $table->foreign('formulir_id', 'point_sales_downpayment_formulir_foreign')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('person_id')->unsigned()->index('point_sales_downpayment_person_index');
            $table->foreign('person_id', 'point_sales_downpayment_person_foreign')
                ->references('id')->on('person')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->integer('sales_order_id')->unsigned()->nullable()->index('point_sales_downpayment_sales_order_index');
            $table->foreign('sales_order_id', 'point_sales_downpayment_sales_order_foreign')
                ->references('id')->on('point_sales_order')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->decimal('amount', 16, 4);
            $table->decimal('remaining_amount', 16, 4);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_sales_downpayment');
    }
}
