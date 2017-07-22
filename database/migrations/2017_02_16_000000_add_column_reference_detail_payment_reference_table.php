<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnReferenceDetailPaymentReferenceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasColumn('point_finance_payment_reference_detail', 'form_reference_id')) {
            Schema::table('point_finance_payment_reference_detail', function ($table) {
                $table->integer('form_reference_id')->after('point_finance_payment_reference_id')->unsigned()->nullable();
                $table->integer('subledger_id')->unsigned()->nullable();
                $table->string('subledger_type')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('point_finance_payment_reference_detail', 'form_reference_id')) {
            Schema::table('point_finance_payment_reference_detail', function ($table) {
                $table->dropColumn('form_reference_id');
                $table->dropColumn('subledger_id');
                $table->dropColumn('subledger_type');
            });
        }
    }
}
