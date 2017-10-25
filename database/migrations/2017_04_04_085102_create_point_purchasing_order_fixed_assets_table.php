<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointPurchasingOrderFixedAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_purchasing_fixed_assets_order', function ($table) {
            $table->increments('id');

            $table->timestamp('required_date')->useCurrent();
            $table->integer('formulir_id')->unsigned()->index('fa_order_formulir');
            $table->foreign('formulir_id')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');
                
            $table->integer('supplier_id')->unsigned()->index('fa_order_supplier_index');
            $table->foreign('supplier_id', 'fa_order_supplier_foreign')
                ->references('id')->on('person')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->boolean('include_expedition')->default(false);
            $table->boolean('is_cash')->default(false);
            $table->decimal('expedition_fee', 16, 4);
            $table->string('type_of_tax');
            $table->decimal('subtotal', 16, 4);
            $table->decimal('discount', 16, 4);
            $table->decimal('tax_base', 16, 4);
            $table->decimal('tax', 16, 4);
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
        Schema::drop('point_purchasing_fixed_assets_order');
    }
}
