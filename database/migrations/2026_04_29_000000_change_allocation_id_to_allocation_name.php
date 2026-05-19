<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ChangeAllocationIdToAllocationName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. point_purchasing_payment_order_detail
        if (Schema::hasColumn('point_purchasing_payment_order_detail', 'allocation_id')) {
            // Drop foreign key if exists (using raw SQL because name might vary)
            try {
                Schema::table('point_purchasing_payment_order_detail', function (Blueprint $table) {
                    $table->dropForeign('pppod_allocation_id_foreign');
                });
            } catch (\Exception $e) {}
            
            DB::statement('ALTER TABLE point_purchasing_payment_order_detail CHANGE allocation_id allocation_name VARCHAR(255) DEFAULT "Without Allocation"');
        }

        // 2. point_purchasing_payment_order_other
        if (Schema::hasColumn('point_purchasing_payment_order_other', 'allocation_id')) {
            try {
                Schema::table('point_purchasing_payment_order_other', function (Blueprint $table) {
                    $table->dropForeign('point_purchasing_payment_order_other_allocation_foreign');
                });
            } catch (\Exception $e) {}
            
            DB::statement('ALTER TABLE point_purchasing_payment_order_other CHANGE allocation_id allocation_name VARCHAR(255) DEFAULT "Without Allocation"');
        }

        // 3. allocation_report
        if (Schema::hasColumn('allocation_report', 'allocation_id')) {
            try {
                Schema::table('allocation_report', function (Blueprint $table) {
                    $table->dropForeign('allocation_report_allocation_id_foreign');
                });
            } catch (\Exception $e) {}
            
            DB::statement('ALTER TABLE allocation_report CHANGE allocation_id allocation_name VARCHAR(255) DEFAULT "Without Allocation"');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Reverse if needed, but for UAT we usually don't need to go back
    }
}
