<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointPurchasingCashAdvanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        self::createTableCashAdvace();
        self::addColumnIncludeAdvance();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('point_purchasing_cash_advance');

        if (Schema::hasColumn('point_purchasing_requisition', 'include_cash_advance')) {
            Schema::table('point_purchasing_requisition', function ($table) {
                $table->dropColumn(['include_cash_advance']);
            });
        }
    }

    public function createTableCashAdvace()
    {
        Schema::create('point_purchasing_cash_advance', function ($table) {
            $table->increments('id');
            
            $table->integer('purchase_requisition_id')->unsigned()->index('point_purchasing_cash_advance_purchase_requisition_index');
            $table->foreign('purchase_requisition_id', 'point_purchasing_cash_advance_purchase_requisition_foreign')
                ->references('id')->on('point_purchasing_requisition')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->integer('formulir_id')->unsigned()->index('point_purchasing_cash_advance_formulir_index');
            $table->foreign('formulir_id', 'point_purchasing_cash_advance_formulir_foreign')
                ->references('id')->on('formulir')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->integer('employee_id')->unsigned()->index('point_purchasing_cash_advance_employee_index');
            $table->foreign('employee_id', 'point_purchasing_cash_advance_employee_foreign')
                ->references('id')->on('person')
                ->onUpdate('restrict')
                ->onDelete('restrict');
            $table->string('payment_type')->nullable()->default('bank');

            $table->decimal('amount', 16, 4);
            $table->decimal('remaining_amount', 16, 4);
        });
    }

    public function addColumnIncludeAdvance()
    {
        if (!Schema::hasColumn('point_purchasing_requisition', 'include_cash_advance')) {
            Schema::table('point_purchasing_requisition', function ($table) {
                $table->boolean('include_cash_advance')->default(false);
            });
        }
    }
}
