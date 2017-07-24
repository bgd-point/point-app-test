<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointPurchasingRequisitionFixedAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('point_purchasing_fixed_assets_requisition', function ($table) {
            $table->increments('id');

            $table->timestamp('required_date')->useCurrent();
            $table->integer('formulir_id')->unsigned()->index('fa_requisition_formulir');
            $table->foreign('formulir_id')
                ->references('id')->on('formulir')
                ->onUpdate('cascade')
                ->onDelete('cascade');
                
            $table->integer('employee_id')->unsigned()->index('fa_requisition_employee_index');
            $table->foreign('employee_id', 'fa_requisition_employee_foreign')
                ->references('id')->on('person')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->integer('supplier_id')->unsigned()->index('fa_requisition_supplier_index')->nullable();
            $table->foreign('supplier_id', 'fa_requisition_supplier_foreign')
                ->references('id')->on('person')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_purchasing_fixed_assets_requisition');
    }
}
