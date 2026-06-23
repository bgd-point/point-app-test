<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToPointPurchasingPaymentOrderDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('point_purchasing_payment_order_detail', function (Blueprint $table) {
            if (!Schema::hasColumn('point_purchasing_payment_order_detail', 'coa_id')) {
                $table->integer('coa_id')->unsigned()->nullable()->after('amount');
            }
            if (!Schema::hasColumn('point_purchasing_payment_order_detail', 'allocation_id')) {
                $table->integer('allocation_id')->unsigned()->nullable()->after('coa_id');
            }
            if (!Schema::hasColumn('point_purchasing_payment_order_detail', 'form_reference_id')) {
                $table->integer('form_reference_id')->unsigned()->nullable()->after('allocation_id');
            }
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
            if (Schema::hasColumn('point_purchasing_payment_order_detail', 'coa_id')) {
                $table->dropColumn('coa_id');
            }
            if (Schema::hasColumn('point_purchasing_payment_order_detail', 'allocation_id')) {
                $table->dropColumn('allocation_id');
            }
            if (Schema::hasColumn('point_purchasing_payment_order_detail', 'form_reference_id')) {
                $table->dropColumn('form_reference_id');
            }
        });
    }
}
