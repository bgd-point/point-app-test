<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointPurchasingBasicPaymentOrderDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_purchasing_basic_payment_order_detail', function ($table) {
            $table->increments('id');
            
            $table->integer('point_purchasing_payment_order_id')->unsigned()->index('point_purchasing_basic_payment_order_detail_bppo_index');
            $table->foreign('point_purchasing_payment_order_id', 'point_purchasing_basic_payment_order_detail_bppo_foreign')
                ->references('id')->on('point_purchasing_basic_payment_order')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->integer('coa_id')->unsigned()->index('purchase_po_basic_detail_coa_index');
            $table->foreign('coa_id', 'purchase_po_basic_detail_coa_foreign')
                ->references('id')->on('coa')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->integer('form_reference_id')->unsigned()->index('purchase_po_basic_detail_form_reference_index');
            $table->foreign('form_reference_id', 'purchase_po_basic_detail_form_reference_foreign')
                    ->references('id')->on('formulir')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            $table->text('detail_notes');
            $table->decimal('amount', 16, 4);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_purchasing_basic_payment_order_detail');
    }
}
