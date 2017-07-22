<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointPurchasingInvoiceFixedAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_purchasing_fixed_assets_invoice', function ($table) {
            $table->increments('id');
            $table->timestamp('due_date')->useCurrent();
            $table->integer('formulir_id')->unsigned()->index('point_purchasing_fixed_assets_formulir_index');
            $table->foreign('formulir_id', 'point_purchasing_fixed_assets_formulir_foreign')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('supplier_id')->unsigned()->index('point_purchasing_fixed_assets_supplier_index');
            $table->foreign('supplier_id', 'point_purchasing_fixed_assets_supplier_foreign')
                ->references('id')->on('person')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->decimal('subtotal', 16, 4);
            $table->decimal('discount', 16, 4);
            $table->decimal('tax_base', 16, 4);
            $table->decimal('tax', 16, 4);
            $table->string('type_of_tax');
            $table->decimal('expedition_fee', 16, 4);
            $table->decimal('total', 16, 4);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_purchasing_fixed_assets_invoice');
    }
}
