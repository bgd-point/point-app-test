<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RevertAllocationNameToId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. point_purchasing_payment_order_detail
        if (Schema::hasColumn('point_purchasing_payment_order_detail', 'allocation_name')) {
            DB::statement('ALTER TABLE point_purchasing_payment_order_detail CHANGE allocation_name allocation_id INT(10) UNSIGNED DEFAULT 1');
            
            Schema::table('point_purchasing_payment_order_detail', function (Blueprint $table) {
                $table->foreign('allocation_id', 'pppod_allocation_id_foreign')->references('id')->on('allocation')->onUpdate('restrict')->onDelete('restrict');
            });
        }

        // 2. point_purchasing_payment_order_other
        if (Schema::hasColumn('point_purchasing_payment_order_other', 'allocation_name')) {
            DB::statement('ALTER TABLE point_purchasing_payment_order_other CHANGE allocation_name allocation_id INT(10) UNSIGNED DEFAULT 1');
            
            Schema::table('point_purchasing_payment_order_other', function (Blueprint $table) {
                $table->foreign('allocation_id', 'point_purchasing_payment_order_other_allocation_foreign')->references('id')->on('allocation')->onUpdate('restrict')->onDelete('restrict');
            });
        }

        // 3. allocation_report
        if (Schema::hasColumn('allocation_report', 'allocation_name')) {
            DB::statement('ALTER TABLE allocation_report CHANGE allocation_name allocation_id INT(10) UNSIGNED DEFAULT 1');
            
            Schema::table('allocation_report', function (Blueprint $table) {
                $table->foreign('allocation_id', 'allocation_report_allocation_id_foreign')->references('id')->on('allocation')->onUpdate('restrict')->onDelete('restrict');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
