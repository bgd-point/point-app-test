<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAllocationIdToPaymentDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('point_purchasing_payment_order_detail')) {
            Schema::table('point_purchasing_payment_order_detail', function (Blueprint $table) {
                if (!Schema::hasColumn('point_purchasing_payment_order_detail', 'allocation_id')) {
                    $table->integer('allocation_id')->unsigned()->default(1)->after('coa_id');
                    $table->foreign('allocation_id', 'pppod_allocation_id_foreign')->references('id')->on('allocation')->onUpdate('restrict')->onDelete('restrict');
                }
            });
        }

        if (Schema::hasTable('point_purchasing_service_payment_order_detail')) {
            Schema::table('point_purchasing_service_payment_order_detail', function (Blueprint $table) {
                if (!Schema::hasColumn('point_purchasing_service_payment_order_detail', 'allocation_id')) {
                    $table->integer('allocation_id')->unsigned()->default(1)->after('coa_id');
                    $table->foreign('allocation_id', 'ppspod_allocation_id_foreign')->references('id')->on('allocation')->onUpdate('restrict')->onDelete('restrict');
                }
            });
        }

        if (Schema::hasTable('point_sales_payment_collection_detail')) {
            Schema::table('point_sales_payment_collection_detail', function (Blueprint $table) {
                if (!Schema::hasColumn('point_sales_payment_collection_detail', 'allocation_id')) {
                    $table->integer('allocation_id')->unsigned()->default(1)->after('coa_id');
                    $table->foreign('allocation_id', 'pspcd_allocation_id_foreign')->references('id')->on('allocation')->onUpdate('restrict')->onDelete('restrict');
                }
            });
        }

        if (Schema::hasTable('point_sales_service_payment_collection_detail')) {
            Schema::table('point_sales_service_payment_collection_detail', function (Blueprint $table) {
                if (!Schema::hasColumn('point_sales_service_payment_collection_detail', 'allocation_id')) {
                    $table->integer('allocation_id')->unsigned()->default(1)->after('coa_id');
                    $table->foreign('allocation_id', 'psspcd_allocation_id_foreign')->references('id')->on('allocation')->onUpdate('restrict')->onDelete('restrict');
                }
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
