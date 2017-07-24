<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointSalesServicePaymentCollectionDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_sales_service_payment_collection_detail', function ($table) {
            $table->increments('id');
            
            $table->integer('point_sales_service_payment_collection_id')->unsigned()->index('point_sales_service_payment_collection_detail_bppo_index');
            $table->foreign('point_sales_service_payment_collection_id', 'point_sales_service_payment_collection_detail_bppo_foreign')
                ->references('id')->on('point_sales_service_payment_collection')
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
        Schema::drop('point_sales_service_payment_collection_detail');
    }
}
