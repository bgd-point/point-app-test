<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnAllocationServiceInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // SERVICE SALES
        if (!Schema::hasColumn('point_sales_service_invoice_service', 'allocation_id')) {
            Schema::table('point_sales_service_invoice_service', function ($table) {
                $table->integer('allocation_id')->unsigned()->index('ps_service_invoice_allocation_serv_index')->default(1);
                $table->foreign('allocation_id', 'ps_service_invoice_allocation_serv_foreign')
                    ->references('id')->on('allocation')
                    ->onUpdate('restrict')
                    ->onDelete('restrict');
            });
        }

        if (!Schema::hasColumn('point_sales_service_invoice_item', 'allocation_id')) {
            Schema::table('point_sales_service_invoice_item', function ($table) {
                $table->integer('allocation_id')->unsigned()->index('ps_service_invoice_allocation_item_index')->default(1);
                $table->foreign('allocation_id', 'ps_service_invoice_allocation_item_foreign')
                    ->references('id')->on('allocation')
                    ->onUpdate('restrict')
                    ->onDelete('restrict');
            });
        }

        if (!Schema::hasColumn('point_sales_service_payment_collection_other', 'allocation_id')) {
            Schema::table('point_sales_service_payment_collection_other', function ($table) {
                $table->integer('allocation_id')->unsigned()->index('ps_service_payment_allocation_index')->default(1);
                $table->foreign('allocation_id', 'ps_service_payment_allocation_foreign')
                    ->references('id')->on('allocation')
                    ->onUpdate('restrict')
                    ->onDelete('restrict');
            });
        }

        // INDIRECT SALES
        Schema::table('point_sales_quotation_item', function ($table) {
            if (!Schema::hasColumn('point_sales_quotation_item', 'allocation_id')) {
                $table->integer('allocation_id')->unsigned()->index('sales_quotation_allocation_item_index')->default(1);
                $table->foreign('allocation_id', 'sales_quotation_allocation_item_foreign')
                    ->references('id')->on('allocation')
                    ->onUpdate('restrict')
                    ->onDelete('restrict');
            }
        });

        if (!Schema::hasColumn('point_sales_order_item', 'allocation_id')) {
            Schema::table('point_sales_order_item', function ($table) {
                $table->integer('allocation_id')->unsigned()->index('sales_order_allocation_item_index')->default(1);
                $table->foreign('allocation_id', 'sales_order_allocation_item_foreign')
                    ->references('id')->on('allocation')
                    ->onUpdate('restrict')
                    ->onDelete('restrict');
            });
        }

        if (!Schema::hasColumn('point_sales_delivery_order_item', 'allocation_id')) {
            Schema::table('point_sales_delivery_order_item', function ($table) {
                $table->integer('allocation_id')->unsigned()->index('sales_delivery_order_allocation_item_index')->default(1);
                $table->foreign('allocation_id', 'sales_delivery_order_allocation_item_foreign')
                    ->references('id')->on('allocation')
                    ->onUpdate('restrict')
                    ->onDelete('restrict');
            });
        }

        if (!Schema::hasColumn('point_sales_retur_item', 'allocation_id')) {
            Schema::table('point_sales_retur_item', function ($table) {
                $table->integer('allocation_id')->unsigned()->index('sales_retur_allocation_item_index')->default(1);
                $table->foreign('allocation_id', 'sales_retur_allocation_item_foreign')
                    ->references('id')->on('allocation')
                    ->onUpdate('restrict')
                    ->onDelete('restrict');
            });
        }

        if (!Schema::hasColumn('point_sales_invoice_item', 'allocation_id')) {
            Schema::table('point_sales_invoice_item', function ($table) {
                $table->integer('allocation_id')->unsigned()->index('sales_invoice_allocation_item_index')->default(1);
                $table->foreign('allocation_id', 'sales_invoice_allocation_item_foreign')
                    ->references('id')->on('allocation')
                    ->onUpdate('restrict')
                    ->onDelete('restrict');
            });
        }


        if (!Schema::hasColumn('point_sales_payment_collection_other', 'allocation_id')) {
            Schema::table('point_sales_payment_collection_other', function ($table) {
                $table->integer('allocation_id')->unsigned()->index('sales_payment_collection_other_allocation_item_index')->default(1);
                $table->foreign('allocation_id', 'sales_payment_collection_other_allocation_item_foreign')
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
        // SERVICE SALES
        if (Schema::hasColumn('point_sales_service_invoice_service', 'allocation_id')) {
            Schema::table('point_sales_service_invoice_service', function ($table) {
                $table->dropForeign('ps_service_invoice_allocation_serv_foreign');
                $table->dropColumn(['allocation_id']);
            });
        }

        if (Schema::hasColumn('point_sales_service_invoice_item', 'allocation_id')) {
            Schema::table('point_sales_service_invoice_item', function ($table) {
                $table->dropForeign('ps_service_invoice_allocation_item_foreign');
                $table->dropColumn(['allocation_id']);
            });
        }

        if (Schema::hasColumn('point_sales_service_payment_collection_other', 'allocation_id')) {
            Schema::table('point_sales_service_payment_collection_other', function ($table) {
                $table->dropForeign('ps_service_payment_allocation_foreign');
                $table->dropColumn(['allocation_id']);
            });
        }

        // INDIRECT SALES
        if (Schema::hasColumn('point_sales_quotation_item', 'allocation_id')) {
            Schema::table('point_sales_quotation_item', function ($table) {
                $table->dropForeign('sales_quotation_allocation_item_foreign');
                $table->dropColumn(['allocation_id']);
            });
        }

        if (Schema::hasColumn('point_sales_order_item', 'allocation_id')) {
            Schema::table('point_sales_order_item', function ($table) {
                $table->dropForeign('sales_order_allocation_item_foreign');
                $table->dropColumn(['allocation_id']);
            });
        }

        if (Schema::hasColumn('point_sales_delivery_order_item', 'allocation_id')) {
            Schema::table('point_sales_delivery_order_item', function ($table) {
                $table->dropForeign('sales_delivery_order_allocation_item_foreign');
                $table->dropColumn(['allocation_id']);
            });
        }

        if (Schema::hasColumn('point_sales_retur_item', 'allocation_id')) {
            Schema::table('point_sales_retur_item', function ($table) {
                $table->dropForeign('sales_retur_allocation_item_foreign');
                $table->dropColumn(['allocation_id']);
            });
        }

        if (Schema::hasColumn('point_sales_invoice_item', 'allocation_id')) {
            Schema::table('point_sales_invoice_item', function ($table) {
                $table->dropForeign('sales_invoice_allocation_item_foreign');
                $table->dropColumn(['allocation_id']);
            });
        }

        if (Schema::hasColumn('point_sales_payment_collection_other', 'allocation_id')) {
            Schema::table('point_sales_payment_collection_other', function ($table) {
                $table->dropForeign('sales_payment_collection_other_allocation_item_foreign');
                $table->dropColumn(['allocation_id']);
            });
        }
    }
}
