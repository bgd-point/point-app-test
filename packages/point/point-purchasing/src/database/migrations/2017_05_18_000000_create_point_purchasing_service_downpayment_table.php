<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointPurchasingServiceDownpaymentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_purchasing_service_downpayment', function ($table) {
            $table->increments('id');
            
            $table->integer('formulir_id')->unsigned()->index('point_purchasing_service_downpayment_formulir_index');
            $table->foreign('formulir_id', 'point_purchasing_service_downpayment_formulir_foreign')
                ->references('id')->on('formulir')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->integer('supplier_id')->unsigned()->index('point_purchasing_service_downpayment_supplier_index');
            $table->foreign('supplier_id', 'point_purchasing_service_downpayment_supplier_foreign')
                ->references('id')->on('person')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->string('payment_type');
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
        Schema::drop('point_purchasing_service_downpayment');
    }
}
