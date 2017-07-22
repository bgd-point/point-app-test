<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointPurchasingServicePaymentOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_purchasing_service_payment_order', function ($table) {
            $table->increments('id');

            $table->timestamp('due_date')->useCurrent();
            
            $table->integer('formulir_id')->unsigned()->index('point_purchasing_service_payment_order_formulir_index');
            $table->foreign('formulir_id', 'point_purchasing_service_payment_order_formulir_foreign')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('person_id')->unsigned()->index('point_purchasing_service_payment_order_person_index');
            $table->foreign('person_id', 'point_purchasing_service_payment_order_person_foreign')
                ->references('id')->on('person')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->decimal('total_payment', 16, 4);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_purchasing_service_payment_order');
    }
}
