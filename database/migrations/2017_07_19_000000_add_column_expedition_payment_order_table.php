<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnExpeditionPaymentOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        self::addColumnExpedition();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        self::dropColumnExpedition();
    }

    public function addColumnExpedition()
    {
        /**
         * TABLE PAYMENT ORDER
         * + Column payment_type
         */
        
        if (!Schema::hasColumn('point_expedition_payment_order', 'payment_type')) {
            Schema::table('point_expedition_payment_order', function ($table) {
                $table->string('payment_type')->nullable()->default('bank');
            });
        }

        /**
         * TABLE PAYMENT ORDER DETAIL
         * + Column coa_id
         * + Column form_reference_id
         */
        if (!Schema::hasColumn('point_expedition_payment_order_detail', 'coa_id')) {
            Schema::table('point_expedition_payment_order_detail', function ($table) {
                $table->integer('coa_id')->unsigned()->index('expedition_po_detail_coa_index');
                $table->foreign('coa_id', 'expedition_po_detail_coa_foreign')
                    ->references('id')->on('coa')
                    ->onUpdate('restrict')
                    ->onDelete('restrict');

            });
        }

        if (!Schema::hasColumn('point_expedition_payment_order_detail', 'form_reference_id')) {
            Schema::table('point_expedition_payment_order_detail', function ($table) {
                $table->integer('form_reference_id')->unsigned()->index('expedition_po_detail_form_reference_index');
                $table->foreign('form_reference_id', 'expedition_po_detail_form_reference_foreign')
                    ->references('id')->on('formulir')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            });
        }        
    }

    public function dropColumnExpedition()
    {
        
        if (Schema::hasColumn('point_expedition_payment_order', 'payment_type')) {
            Schema::table('point_expedition_payment_order', function ($table) {
                $table->dropColumn(['payment_type']);
            });
        }

        if (Schema::hasColumn('point_expedition_payment_order_detail', 'coa_id')) {
            Schema::table('point_expedition_payment_order_detail', function ($table) {
                $table->dropForeign('expedition_po_detail_coa_foreign');
                $table->dropIndex('expedition_po_detail_coa_index');
                $table->dropColumn(['coa_id']);

            });
        }

        if (Schema::hasColumn('point_expedition_payment_order_detail', 'form_reference_id')) {
            Schema::table('point_expedition_payment_order_detail', function ($table) {
                $table->dropForeign('expedition_po_detail_form_reference_foreign');
                $table->dropIndex('expedition_po_detail_form_reference_index');
                $table->dropColumn(['form_reference_id']);
            });
        } 
    }
}
