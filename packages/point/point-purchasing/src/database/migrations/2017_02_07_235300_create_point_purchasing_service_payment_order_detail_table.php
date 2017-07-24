<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointPurchasingServicePaymentOrderDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_purchasing_service_payment_order_detail', function ($table) {
            $table->increments('id');
            
            $table->integer('point_purchasing_service_payment_order_id')->unsigned()->index('point_purchasing_service_payment_order_detail_bppo_index');
            $table->foreign('point_purchasing_service_payment_order_id', 'point_purchasing_service_payment_order_detail_bppo_foreign')
                ->references('id')->on('point_purchasing_service_payment_order')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->text('detail_notes');
            $table->decimal('amount', 16, 4);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_purchasing_service_payment_order_detail');
    }
}
