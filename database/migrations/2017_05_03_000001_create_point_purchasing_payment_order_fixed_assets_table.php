<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointPurchasingPaymentOrderFixedAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_purchasing_fixed_assets_payment_order', function ($table) {
            $table->increments('id');
            
            $table->integer('formulir_id')->unsigned()->index('point_purchasing_fa_payment_order_formulir_index');
            $table->foreign('formulir_id', 'point_purchasing_fa_payment_order_formulir_foreign')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('supplier_id')->unsigned()->index('point_purchasing_fa_payment_order_supplier_index');
            $table->foreign('supplier_id', 'point_purchasing_fa_payment_order_supplier_foreign')
                ->references('id')->on('person')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->string('payment_type');
            $table->decimal('total_payment', 16, 4);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_purchasing_fixed_assets_payment_order');
    }
}
