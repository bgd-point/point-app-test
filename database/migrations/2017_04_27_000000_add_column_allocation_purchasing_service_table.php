<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnAllocationPurchasingServiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('point_purchasing_service_invoice_service', 'allocation_id')) {
            Schema::table('point_purchasing_service_invoice_service', function ($table) {
                $table->integer('allocation_id')->unsigned()->index('purchasing_service_invoice_allocation_serv_index')->default(1);
                $table->foreign('allocation_id', 'purchasing_service_invoice_allocation_serv_foreign')
                    ->references('id')->on('allocation')
                    ->onUpdate('restrict')
                    ->onDelete('restrict');
            });
        }

        if (!Schema::hasColumn('point_purchasing_service_invoice_item', 'allocation_id')) {
            Schema::table('point_purchasing_service_invoice_item', function ($table) {
                $table->integer('allocation_id')->unsigned()->index('purchasing_service_invoice_allocation_item_index')->default(1);
                $table->foreign('allocation_id', 'purchasing_service_invoice_allocation_item_foreign')
                    ->references('id')->on('allocation')
                    ->onUpdate('restrict')
                    ->onDelete('restrict');
            });
        }

        if (!Schema::hasColumn('point_purchasing_service_payment_order_other', 'allocation_id')) {
            Schema::table('point_purchasing_service_payment_order_other', function ($table) {
                $table->integer('allocation_id')->unsigned()->index('purchasing_service_payment_allocation_index')->default(1);
                $table->foreign('allocation_id', 'purchasing_service_payment_allocation_foreign')
                    ->references('id')->on('allocation')
                    ->onUpdate('restrict')
                    ->onDelete('restrict');
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
        if (Schema::hasColumn('point_purchasing_service_invoice_service', 'allocation_id')) {
            Schema::table('point_purchasing_service_invoice_service', function ($table) {
                $table->dropForeign('purchasing_service_invoice_allocation_serv_foreign');
                $table->dropIndex('purchasing_service_invoice_allocation_serv_index');
                $table->dropColumn(['allocation_id']);
            });
        }

        if (Schema::hasColumn('point_purchasing_service_payment_order_detail', 'allocation_id')) {
            Schema::table('point_purchasing_service_payment_order_detail', function ($table) {
                $table->dropForeign('purchasing_service_invoice_allocation_item_foreign');
                $table->dropIndex('purchasing_service_invoice_allocation_item_index');
                $table->dropColumn(['allocation_id']);
            });
        }

        if (Schema::hasColumn('point_purchasing_service_payment_order_other', 'allocation_id')) {
            Schema::table('point_purchasing_service_payment_order_other', function ($table) {
                $table->dropForeign('purchasing_service_payment_allocation_foreign');
                $table->dropIndex('purchasing_service_payment_allocation_index');
                $table->dropColumn(['allocation_id']);
            });
        }
    }
}
