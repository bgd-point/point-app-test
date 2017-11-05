<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAllocationServicePaymentCollectionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('point_sales_service_payment_collection_detail', function ($table) {
            $table->integer('allocation_id')->unsigned()->default(1)->index('point_sales_service_detail_allocation_index');
            $table->foreign('allocation_id', 'point_sales_service_detail_allocation_foreign')
                ->references('id')->on('allocation')
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
        Schema::table('point_sales_service_payment_collection_detail', function ($table) {
            $table->dropForeign('allocation_id');
            $table->dropColumn('allocation_id');
        });
    }
}
