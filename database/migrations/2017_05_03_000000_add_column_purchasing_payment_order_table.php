<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnPurchasingPaymentOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        self::addColumnPurchaseInventory();
        self::addColumnPurchaseService();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        self::dropColumnPurchaseInventory();
        self::dropColumnPurchaseService();
    }

    public function addColumnPurchaseInventory()
    {
        /**
         * TABLE PAYMENT ORDER
         * + Column payment_type
         */
        
        if (!Schema::hasColumn('point_purchasing_payment_order', 'payment_type')) {
            Schema::table('point_purchasing_payment_order', function ($table) {
                $table->string('payment_type')->nullable()->default('bank');
            });
        }

        /**
         * TABLE PAYMENT ORDER DETAIL
         * + Column coa_id
         * + Column form_reference_id
         */
        if (!Schema::hasColumn('point_purchasing_payment_order_detail', 'coa_id')) {
            Schema::table('point_purchasing_payment_order_detail', function ($table) {
                $table->integer('coa_id')->unsigned()->index('purchase_po_detail_coa_index');
                $table->foreign('coa_id', 'purchase_po_detail_coa_foreign')
                    ->references('id')->on('coa')
                    ->onUpdate('restrict')
                    ->onDelete('restrict');

            });
        }

        if (!Schema::hasColumn('point_purchasing_payment_order_detail', 'form_reference_id')) {
            Schema::table('point_purchasing_payment_order_detail', function ($table) {
                $table->integer('form_reference_id')->unsigned()->index('purchase_po_detail_form_reference_index');
                $table->foreign('form_reference_id', 'purchase_po_detail_form_reference_foreign')
                    ->references('id')->on('formulir')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            });
        }        
    }

    public function dropColumnPurchaseInventory()
    {
        
        if (Schema::hasColumn('point_purchasing_payment_order', 'payment_type')) {
            Schema::table('point_purchasing_payment_order', function ($table) {
                $table->dropColumn(['payment_type']);
            });
        }

        if (Schema::hasColumn('point_purchasing_payment_order_detail', 'coa_id')) {
            Schema::table('point_purchasing_payment_order_detail', function ($table) {
                $table->dropForeign('purchase_po_detail_coa_foreign');
                $table->dropIndex('purchase_po_detail_coa_index');
                $table->dropColumn(['coa_id']);

            });
        }

        if (Schema::hasColumn('point_purchasing_payment_order_detail', 'form_reference_id')) {
            Schema::table('point_purchasing_payment_order_detail', function ($table) {
                $table->dropForeign('purchase_po_detail_form_reference_foreign');
                $table->dropIndex('purchase_po_detail_form_reference_index');
                $table->dropColumn(['form_reference_id']);
            });
        } 
    }

    public function addColumnPurchaseService()
    {
        /**
         * TABLE PAYMENT ORDER
         * + Column payment_type
         */
        
        if (!Schema::hasColumn('point_purchasing_service_payment_order', 'payment_type')) {
            Schema::table('point_purchasing_service_payment_order', function ($table) {
                $table->string('payment_type')->nullable()->default('bank');
            });
        }

        /**
         * TABLE PAYMENT ORDER DETAIL
         * + Column coa_id
         * + Column form_reference_id
         */
        if (!Schema::hasColumn('point_purchasing_service_payment_order_detail', 'coa_id')) {
            Schema::table('point_purchasing_service_payment_order_detail', function ($table) {
                $table->integer('coa_id')->unsigned()->index('purchase_po_service_detail_coa_index');
                $table->foreign('coa_id', 'purchase_po_service_detail_coa_foreign')
                    ->references('id')->on('coa')
                    ->onUpdate('restrict')
                    ->onDelete('restrict');

            });
        }

        if (!Schema::hasColumn('point_purchasing_service_payment_order_detail', 'form_reference_id')) {
            Schema::table('point_purchasing_service_payment_order_detail', function ($table) {
                $table->integer('form_reference_id')->unsigned()->index('purchase_po_service_detail_form_reference_index');
                $table->foreign('form_reference_id', 'purchase_po_service_detail_form_reference_foreign')
                    ->references('id')->on('formulir')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            });
        }        
    }

    public function dropColumnPurchaseService()
    {
        
        if (Schema::hasColumn('point_purchasing_service_payment_order', 'payment_type')) {
            Schema::table('point_purchasing_service_payment_order', function ($table) {
                $table->dropColumn(['payment_type']);
            });
        }

        if (Schema::hasColumn('point_purchasing_service_payment_order_detail', 'coa_id')) {
            Schema::table('point_purchasing_service_payment_order_detail', function ($table) {
                $table->dropForeign('purchase_po_service_detail_coa_foreign');
                $table->dropIndex('purchase_po_service_detail_coa_index');
                $table->dropColumn(['coa_id']);

            });
        }

        if (Schema::hasColumn('point_purchasing_service_payment_order_detail', 'form_reference_id')) {
            Schema::table('point_purchasing_service_payment_order_detail', function ($table) {
                $table->dropForeign('purchase_po_service_detail_form_reference_foreign');
                $table->dropIndex('purchase_po_service_detail_form_reference_index');
                $table->dropColumn(['form_reference_id']);
            });
        } 
    }
}
