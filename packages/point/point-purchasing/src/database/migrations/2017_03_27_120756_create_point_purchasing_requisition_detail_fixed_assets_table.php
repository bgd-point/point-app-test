<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointPurchasingRequisitionDetailFixedAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_purchasing_fixed_assets_requisition_detail', function ($table) {
            $table->increments('id');
            
            $table->integer('fixed_assets_requisition_id')->unsigned()->index('requisition_fa_detail_index');
            $table->foreign('fixed_assets_requisition_id', 'requisition_fa_detail_foreign')
                ->references('id')->on('point_purchasing_fixed_assets_requisition')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('coa_id')->unsigned()->index('requisition_fa_detail_coa_index');
            $table->foreign('coa_id', 'requisition_fa_detail_coa_foreign')
                ->references('id')->on('coa')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->integer('allocation_id')->unsigned()->index('requisition_fa_detail_allocation_index');
            $table->foreign('allocation_id', 'requisition_fa_detail_allocation_foreign')
                ->references('id')->on('allocation')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            
            $table->string('name');
            $table->decimal('quantity', 16, 4);
            $table->decimal('price', 16, 4);
            $table->string('unit');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_purchasing_fixed_assets_requisition_detail');
    }
}
