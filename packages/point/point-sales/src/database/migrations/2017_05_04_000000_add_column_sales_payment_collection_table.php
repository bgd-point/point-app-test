<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnSalesPaymentCollectionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        self::addColumnSalesInventory();
        self::addColumnSalesService();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        self::dropColumnSalesInventory();
        self::dropColumnSalesService();
    }

    public function addColumnSalesInventory()
    {
        /**
         * TABLE PAYMENT COLLECTION
         * + Column payment_type
         */
        
        if (!Schema::hasColumn('point_sales_payment_collection', 'payment_type')) {
            Schema::table('point_sales_payment_collection', function ($table) {
                $table->string('payment_type')->nullable()->default('bank');
            });
        }

        /**
         * TABLE PAYMENT COLLECTION DETAIL
         * + Column coa_id
         * + Column form_reference_id
         */
        if (!Schema::hasColumn('point_sales_payment_collection_detail', 'coa_id')) {
            Schema::table('point_sales_payment_collection_detail', function ($table) {
                $table->integer('coa_id')->unsigned()->index('sales_pc_detail_coa_index');
                $table->foreign('coa_id', 'sales_pc_detail_coa_foreign')
                    ->references('id')->on('coa')
                    ->onUpdate('restrict')
                    ->onDelete('restrict');

            });
        }

        if (!Schema::hasColumn('point_sales_payment_collection_detail', 'form_reference_id')) {
            Schema::table('point_sales_payment_collection_detail', function ($table) {
                $table->integer('form_reference_id')->unsigned()->index('sales_pc_detail_form_reference_index');
                $table->foreign('form_reference_id', 'sales_pc_detail_form_reference_foreign')
                    ->references('id')->on('formulir')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            });
        }        
    }

    public function dropColumnSalesInventory()
    {
        
        if (Schema::hasColumn('point_sales_payment_collection', 'payment_type')) {
            Schema::table('point_sales_payment_collection', function ($table) {
                $table->dropColumn(['payment_type']);
            });
        }

        if (Schema::hasColumn('point_sales_payment_collection_detail', 'coa_id')) {
            Schema::table('point_sales_payment_collection_detail', function ($table) {
                $table->dropForeign('sales_pc_detail_coa_foreign');
                $table->dropIndex('sales_pc_detail_coa_index');
                $table->dropColumn(['coa_id']);

            });
        }

        if (Schema::hasColumn('point_sales_payment_collection_detail', 'form_reference_id')) {
            Schema::table('point_sales_payment_collection_detail', function ($table) {
                $table->dropForeign('sales_pc_detail_form_reference_foreign');
                $table->dropIndex('sales_pc_detail_form_reference_index');
                $table->dropColumn(['form_reference_id']);
            });
        } 
    }

    public function addColumnSalesService()
    {
        /**
         * TABLE PAYMENT COLLECTION
         * + Column payment_type
         */
        
        if (!Schema::hasColumn('point_sales_service_payment_collection', 'payment_type')) {
            Schema::table('point_sales_service_payment_collection', function ($table) {
                $table->string('payment_type')->nullable()->default('bank');
            });
        }

        /**
         * TABLE PAYMENT COLLECTION DETAIL
         * + Column coa_id
         * + Column form_reference_id
         */
        if (!Schema::hasColumn('point_sales_service_payment_collection_detail', 'coa_id')) {
            Schema::table('point_sales_service_payment_collection_detail', function ($table) {
                $table->integer('coa_id')->unsigned()->index('sales_pc_service_detail_coa_index');
                $table->foreign('coa_id', 'sales_pc_service_detail_coa_foreign')
                    ->references('id')->on('coa')
                    ->onUpdate('restrict')
                    ->onDelete('restrict');

            });
        }

        if (!Schema::hasColumn('point_sales_service_payment_collection_detail', 'form_reference_id')) {
            Schema::table('point_sales_service_payment_collection_detail', function ($table) {
                $table->integer('form_reference_id')->unsigned()->index('sales_pc_service_detail_form_reference_index');
                $table->foreign('form_reference_id', 'sales_pc_service_detail_form_reference_foreign')
                    ->references('id')->on('formulir')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            });
        }        
    }

    public function dropColumnSalesService()
    {
        
        if (Schema::hasColumn('point_sales_service_payment_collection', 'payment_type')) {
            Schema::table('point_sales_service_payment_collection', function ($table) {
                $table->dropColumn(['payment_type']);
            });
        }

        if (Schema::hasColumn('point_sales_service_payment_collection_detail', 'coa_id')) {
            Schema::table('point_sales_service_payment_collection_detail', function ($table) {
                $table->dropForeign('sales_pc_service_detail_coa_foreign');
                $table->dropIndex('sales_pc_service_detail_coa_index');
                $table->dropColumn(['coa_id']);

            });
        }

        if (Schema::hasColumn('point_sales_service_payment_collection_detail', 'form_reference_id')) {
            Schema::table('point_sales_service_payment_collection_detail', function ($table) {
                $table->dropForeign('sales_pc_service_detail_form_reference_foreign');
                $table->dropIndex('sales_pc_service_detail_form_reference_index');
                $table->dropColumn(['form_reference_id']);
            });
        } 
    }
}
