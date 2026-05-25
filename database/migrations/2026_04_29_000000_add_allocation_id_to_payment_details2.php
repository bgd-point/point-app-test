<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAllocationIdToPaymentDetails2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('point_purchasing_payment_order_detail', function (Blueprint $table) {
            $table->integer('allocation_id')->unsigned()->default(1);
            $table->foreign('allocation_id', 'pppod_allocation_id_foreign')->references('id')->on('allocation')->onUpdate('restrict')->onDelete('restrict');
        });
        Schema::table('point_purchasing_service_payment_order_detail', function (Blueprint $table) {
            $table->integer('allocation_id')->unsigned()->default(1);
            $table->foreign('allocation_id', 'ppspod_allocation_id_foreign')->references('id')->on('allocation')->onUpdate('restrict')->onDelete('restrict');
        });

        Schema::table('point_sales_payment_collection_detail', function (Blueprint $table) {
            $table->integer('allocation_id')->unsigned()->default(1);
            $table->foreign('allocation_id', 'pspcd_allocation_id_foreign')->references('id')->on('allocation')->onUpdate('restrict')->onDelete('restrict');
        });

        Schema::table('point_sales_service_payment_collection_detail', function (Blueprint $table) {
            $table->integer('allocation_id')->unsigned()->default(1);
            $table->foreign('allocation_id', 'psspcd_allocation_id_foreign')->references('id')->on('allocation')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('point_purchasing_payment_order_detail', function (Blueprint $table) {
            $table->dropForeign('pppod_allocation_id_foreign');
            $table->dropColumn('allocation_id');
        });

        Schema::table('point_purchasing_service_payment_order_detail', function (Blueprint $table) {
            $table->dropForeign('ppspod_allocation_id_foreign');
            $table->dropColumn('allocation_id');
        });

        Schema::table('point_sales_payment_collection_detail', function (Blueprint $table) {
            $table->dropForeign('pspcd_allocation_id_foreign');
            $table->dropColumn('allocation_id');
        });

        Schema::table('point_sales_service_payment_collection_detail', function (Blueprint $table) {
            $table->dropForeign('psspcd_allocation_id_foreign');
            $table->dropColumn('allocation_id');
        });
    }
}
